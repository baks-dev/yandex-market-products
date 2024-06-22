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
use BaksDev\Yandex\Market\Products\Messenger\YaMarketProductsStocksUpdate\YaMarketProductsStocksMessage;
use BaksDev\Yandex\Market\Products\Repository\Card\CardByCriteria\YaMarketProductsCardByCriteriaInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(priority: 0)]
final class UpdateStocksYaMarketByRecalculate
{
    private YaMarketProductsCardByCriteriaInterface $cardByCriteria;
    private MessageDispatchInterface $messageDispatch;

    public function __construct(
        YaMarketProductsCardByCriteriaInterface $cardByCriteria,
        MessageDispatchInterface $messageDispatch
    ) {
        $this->cardByCriteria = $cardByCriteria;
        $this->messageDispatch = $messageDispatch;
    }

    /**
     * Отправляем сообщение на обновление остатков при обновлении складского учета
     */
    public function __invoke(RecalculateProductMessage $product): void
    {
        $cards = $this->cardByCriteria
            ->product($product->getProduct())
            ->offer($product->getOffer())
            ->variation($product->getVariation())
            ->modification($product->getModification())
            ->findAll();

        if($cards->valid())
        {
            /** @var YaMarketProductsStocksMessage $YaMarketProductsCardMessage */
            foreach($cards as $YaMarketProductsCardMessage)
            {
                /** Транспорт yandex-market-products чтобы не мешать общей очереди */
                $YaMarketProductsStocksMessage = new YaMarketProductsStocksMessage($YaMarketProductsCardMessage);
                $this->messageDispatch->dispatch($YaMarketProductsStocksMessage, transport: 'yandex-market-products');
            }
        }
    }
}
