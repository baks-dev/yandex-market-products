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

namespace BaksDev\Yandex\Market\Products\Messenger\YaMarketProductsStocksUpdate;

use BaksDev\Core\Messenger\MessageDelay;
use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Yandex\Market\Products\Api\Products\Stocks\YaMarketProductGetStocksRequest;
use BaksDev\Yandex\Market\Products\Api\Products\Stocks\YaMarketProductUpdateStocksRequest;
use BaksDev\Yandex\Market\Products\Repository\Card\CurrentYaMarketProductsCard\YaMarketProductsCardInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class YaMarketProductsStocksUpdate
{
    private LoggerInterface $logger;

    public function __construct(
        private readonly YaMarketProductGetStocksRequest $marketProductStocksGetRequest,
        private readonly YaMarketProductUpdateStocksRequest $marketProductStocksUpdateRequest,
        private readonly YaMarketProductsCardInterface $marketProductsCard,
        private readonly MessageDispatchInterface $messageDispatch,
        LoggerInterface $yandexMarketProductsLogger,
    )
    {
        $this->logger = $yandexMarketProductsLogger;
    }

    /**
     * Обновляем остатки товаров Yandex Market
     */
    public function __invoke(YaMarketProductsStocksMessage $message): void
    {
        $Card = $this->marketProductsCard
            ->forProduct($message->getProduct())
            ->forOfferConst($message->getOfferConst())
            ->forVariationConst($message->getVariationConst())
            ->forModificationConst($message->getModificationConst())
            ->find();

        if($Card === false)
        {
            return;
        }

        /** Не обновляем остатки карточки без цены */
        if(empty($Card['product_price']))
        {
            return;
        }

        /** Возвращает данные об остатках товаров н маркетплейсе */
        $ProductStocks = $this->marketProductStocksGetRequest
            ->profile($message->getProfile())
            ->article($Card['article'])
            ->find();

        if(false === $ProductStocks)
        {
            $this->messageDispatch->dispatch(
                message: $message,
                stamps: [new MessageDelay('5 seconds')],
                transport: 'yandex-market-products',
            );

            $this->logger->critical(sprintf('Пробуем обновить остатки артикула %s через 5 секунд', $Card['article']));

            return;
        }

        $product_quantity = isset($Card['product_quantity']) ? max($Card['product_quantity'], 0) : 0;

        if($ProductStocks === $product_quantity)
        {
            $this->logger->info(sprintf(
                'Наличие соответствует %s: %s == %s',
                $Card['article'],
                $ProductStocks,
                $product_quantity
            ), [$message->getProfile()]);

            return;
        }

        /** Обновляем остатки товара если наличие изменилось */
        $this->marketProductStocksUpdateRequest
            ->profile($message->getProfile())
            ->article($Card['article'])
            ->total($product_quantity)
            ->update();

        $this->logger->info(sprintf(
            'Обновили наличие %s: %s => %s',
            $Card['article'],
            $ProductStocks,
            $product_quantity
        ), [$message->getProfile()]);

    }
}
