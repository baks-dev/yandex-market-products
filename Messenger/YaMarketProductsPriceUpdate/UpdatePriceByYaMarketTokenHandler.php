<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Yandex\Market\Products\Messenger\YaMarketProductsPriceUpdate;

use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Products\Product\Repository\AllProductsIdentifier\AllProductsIdentifierInterface;
use BaksDev\Yandex\Market\Messenger\YaMarketTokenMessage;
use BaksDev\Yandex\Market\Products\Messenger\Card\YaMarketProductsCardMessage;
use BaksDev\Yandex\Market\Repository\AllProfileToken\AllProfileYaMarketTokenInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(priority: 0)]
final readonly class UpdatePriceByYaMarketTokenHandler
{
    public function __construct(
        private AllProductsIdentifierInterface $allProductsIdentifier,
        private AllProfileYaMarketTokenInterface $allProfileToken,
        private MessageDispatchInterface $messageDispatch,
    ) {}

    /**
     * Обновляем цены при обновлении настроек кена и торговой наценки
     */
    public function __invoke(YaMarketTokenMessage $message): void
    {

        /* Получаем все имеющиеся карточки в системе */
        $products = $this->allProductsIdentifier->findAll();

        if($products === false)
        {
            return;
        }

        /** Получаем активные токены авторизации профилей */
        $profiles = $this->allProfileToken
            ->onlyActiveToken()
            ->findAll();

        if(false === $profiles->valid())
        {
            return;
        }

        $profiles = iterator_to_array($profiles);

        foreach($products as $product)
        {
            foreach($profiles as $profile)
            {
                $YaMarketProductsCardMessage = new YaMarketProductsCardMessage(
                    $profile,
                    $product['product_id'],
                    $product['offer_const'],
                    $product['variation_const'],
                    $product['modification_const'],
                );

                $YaMarketProductsPriceMessage = new YaMarketProductsPriceMessage($YaMarketProductsCardMessage);

                $this->messageDispatch
                    ->dispatch(
                        message: $YaMarketProductsPriceMessage,
                        transport: 'yandex-market-products'
                    );
            }
        }
    }
}
