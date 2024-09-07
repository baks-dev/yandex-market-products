<?php
/*
 *  Copyright 2024.  Baks.dev <admin@baks.dev>
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

declare(strict_types=1);

namespace BaksDev\Yandex\Market\Products\Messenger\ProductStocks;

use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Products\Stocks\Messenger\Products\Recalculate\RecalculateProductMessage;
use BaksDev\Yandex\Market\Products\Messenger\Card\YaMarketProductsCardMessage;
use BaksDev\Yandex\Market\Products\Messenger\YaMarketProductsStocksUpdate\YaMarketProductsStocksMessage;
use BaksDev\Yandex\Market\Repository\AllProfileToken\AllProfileYaMarketTokenInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(priority: 0)]
final class UpdateStocksYaMarketByRecalculate
{
    public function __construct(
        private readonly AllProfileYaMarketTokenInterface $allProfileYaMarketToken,
        private readonly MessageDispatchInterface $messageDispatch
    ) {}

    /**
     * Отправляем сообщение на обновление остатков при обновлении складского учета
     */
    public function __invoke(RecalculateProductMessage $product): void
    {
        /**  Получаем активные токены профилей пользователя */

        $profiles = $this
            ->allProfileYaMarketToken
            ->onlyActiveToken()
            ->findAll();


        if($profiles->valid() === false)
        {
            return;
        }

        foreach($profiles as $profile)
        {
            $YaMarketProductsCardMessage = new YaMarketProductsCardMessage(
                $profile,
                $product->getProduct(),
                $product->getOffer(),
                $product->getVariation(),
                $product->getModification()
            );

            /** Транспорт yandex-market-products чтобы не мешать общей очереди */
            $YaMarketProductsStocksMessage = new YaMarketProductsStocksMessage($YaMarketProductsCardMessage);
            $this->messageDispatch->dispatch($YaMarketProductsStocksMessage, transport: 'yandex-market-products');
        }
    }
}
