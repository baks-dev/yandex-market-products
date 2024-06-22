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

use BaksDev\Core\Lock\AppLockInterface;
use BaksDev\Yandex\Market\Products\Api\Products\Stocks\YandexMarketProductStocksGetRequest;
use BaksDev\Yandex\Market\Products\Api\Products\Stocks\YandexMarketProductStocksUpdateRequest;
use BaksDev\Yandex\Market\Products\Repository\Card\CurrentYaMarketProductsStocks\YaMarketProductsStocksInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\RecoverableMessageHandlingException;

#[AsMessageHandler]
final class YaMarketProductsStocksUpdate
{
    private LoggerInterface $logger;

    public function __construct(
        private readonly YandexMarketProductStocksGetRequest $marketProductStocksGetRequest,
        private readonly YandexMarketProductStocksUpdateRequest $marketProductStocksUpdateRequest,
        private readonly YaMarketProductsStocksInterface $marketProductsCard,
        private readonly AppLockInterface $appLock,
        LoggerInterface $yandexMarketProductsLogger,
    ) {

        $this->logger = $yandexMarketProductsLogger;

    }

    /**
     * Обновляем остатки товаров Yandex Market
     */
    public function __invoke(YaMarketProductsStocksMessage $message): void
    {
        $Card = $this->marketProductsCard->findByCard($message->getId());

        if(!$Card)
        {
            return;
        }

        /** Не обновляем остатки карточки без цены */
        if(empty($Card['product_price']))
        {
            return;
        }

        /** Добавляем лок на процесс, остатки обновляются в порядке очереди! */
        $lock = $this->appLock
            ->createLock([$Card['profile'], $Card['article'], self::class]);

        /** Если процесс заблокирован - придем позже */
        if($lock->isLock())
        {
            throw new RecoverableMessageHandlingException(sprintf('Остатки артикула %s уже обновляются! попробуем позже ... ', $Card['article']));
        }

        $ProductStocks = $this->marketProductStocksGetRequest
            ->profile($Card['profile'])
            ->article($Card['article'])
            ->find();

        $product_quantity = max($Card['product_quantity'], 0);

        /** Обновляем остатки товара если наличие изменилось */
        if($ProductStocks !== $product_quantity)
        {
            $this->marketProductStocksUpdateRequest
                ->profile($Card['profile'])
                ->article($Card['article'])
                ->total($product_quantity)
                ->update();

            $this->logger->info(sprintf('Обновили наличие товара %s: %s => %s', $Card['article'], $ProductStocks, $Card['product_quantity']));
        }

        $lock->release();
    }
}
