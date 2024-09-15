<?php
/*
 *  Copyright 2023.  Baks.dev <admin@baks.dev>
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
use BaksDev\Products\Stocks\Entity\Event\ProductStockEvent;
use BaksDev\Products\Stocks\Entity\Products\ProductStockProduct;
use BaksDev\Products\Stocks\Messenger\ProductStockMessage;
use BaksDev\Products\Stocks\Repository\ProductStocksById\ProductStocksByIdInterface;
use BaksDev\Products\Stocks\Type\Status\ProductStockStatus\ProductStockStatusIncoming;
use BaksDev\Yandex\Market\Products\Messenger\Card\YaMarketProductsCardMessage;
use BaksDev\Yandex\Market\Products\Messenger\YaMarketProductsStocksUpdate\YaMarketProductsStocksMessage;
use BaksDev\Yandex\Market\Repository\AllProfileToken\AllProfileYaMarketTokenInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Stamp\DelayStamp;

#[AsMessageHandler]
final readonly class UpdateStocksYaMarketByIncoming
{
    public function __construct(
        private ProductStocksByIdInterface $productStocks,
        private EntityManagerInterface $entityManager,
        private MessageDispatchInterface $messageDispatch,
        private AllProfileYaMarketTokenInterface $allProfileYaMarketToken,
    ) {}

    /**
     * Обновляет остатки при поступлении продукции на склад
     */
    public function __invoke(ProductStockMessage $message): void
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

        /** Получаем статус заявки */
        $ProductStockEvent = $this->entityManager
            ->getRepository(ProductStockEvent::class)
            ->find($message->getEvent());

        $this->entityManager->clear();

        if(!$ProductStockEvent)
        {
            return;
        }

        /**
         * Если Статус заявки не является Incoming «Приход на склад»
         */
        if(false === $ProductStockEvent->getStatus()->equals(ProductStockStatusIncoming::class))
        {
            return;
        }

        // Получаем всю продукцию в ордере со статусом Incoming
        $products = $this->productStocks->getProductsIncomingStocks($message->getId());

        if(empty($products))
        {
            return;
        }

        foreach($profiles as $profile)
        {
            /** @var ProductStockProduct $product */
            foreach($products as $product)
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

                $this->messageDispatch->dispatch(
                    $YaMarketProductsStocksMessage,
                    stamps: [new DelayStamp(2000)], // задержка 2 сек для обновления карточки
                    transport: 'yandex-market-products'
                );

            }
        }
    }

}
