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
use BaksDev\Yandex\Market\Products\Repository\Card\CardByCriteria\YaMarketProductsCardByCriteriaInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(priority: 1)]
final class ProductStocksTotalByIncoming
{
    private ProductStocksByIdInterface $productStocks;
    private EntityManagerInterface $entityManager;
    private MessageDispatchInterface $messageDispatch;
    private YaMarketProductsCardByCriteriaInterface $cardByCriteria;

    public function __construct(
        ProductStocksByIdInterface $productStocks,
        EntityManagerInterface $entityManager,
        MessageDispatchInterface $messageDispatch,
        YaMarketProductsCardByCriteriaInterface $cardByCriteria
    )
    {
        $this->productStocks = $productStocks;
        $this->entityManager = $entityManager;
        $this->messageDispatch = $messageDispatch;
        $this->cardByCriteria = $cardByCriteria;
    }

    /**
     * Обновляет складские остатки при поступлении на склад
     */
    public function __invoke(ProductStockMessage $message): void
    {
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

        /** Идентификатор профиля склада при поступлении */
        $UserProfileUid = $ProductStockEvent->getProfile();

        /** @var ProductStockProduct $product */
        foreach($products as $product)
        {
            $YaMarketProductsCardMessage = $this->cardByCriteria
                ->product($product->getProduct())
                ->offer($product->getOffer())
                ->variation($product->getVariation())
                ->modification($product->getModification())
                ->findByProfile($UserProfileUid);


            if($YaMarketProductsCardMessage)
            {
                // Отправляем сообщение на обновление остатков транспорт профиля (пополнение не имеет приоритета)
                $this->messageDispatch->dispatch($YaMarketProductsCardMessage, transport: (string) $UserProfileUid);
            }
        }
    }

}
