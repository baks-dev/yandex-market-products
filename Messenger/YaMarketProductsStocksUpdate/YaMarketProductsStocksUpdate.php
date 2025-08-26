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

namespace BaksDev\Yandex\Market\Products\Messenger\YaMarketProductsStocksUpdate;

use BaksDev\Core\Messenger\MessageDelay;
use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Orders\Order\Repository\ProductTotalInOrders\ProductTotalInOrdersInterface;
use BaksDev\Products\Stocks\BaksDevProductsStocksBundle;
use BaksDev\Products\Stocks\Repository\ProductWarehouseTotal\ProductWarehouseTotalInterface;
use BaksDev\Yandex\Market\Products\Api\Products\Stocks\GetYaMarketProductStocksRequest;
use BaksDev\Yandex\Market\Products\Api\Products\Stocks\UpdateYaMarketProductStocksRequest;
use BaksDev\Yandex\Market\Products\Repository\Card\CurrentYaMarketProductsCard\CurrentYaMarketProductCardInterface;
use BaksDev\Yandex\Market\Products\Repository\Card\CurrentYaMarketProductsCard\CurrentYaMarketProductCardResult;
use BaksDev\Yandex\Market\Repository\YaMarketTokensByProfile\YaMarketTokensByProfileInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class YaMarketProductsStocksUpdate
{
    public function __construct(
        #[Target('yandexMarketProductsLogger')] private LoggerInterface $logger,
        private GetYaMarketProductStocksRequest $marketProductStocksGetRequest,
        private UpdateYaMarketProductStocksRequest $marketProductStocksUpdateRequest,
        private CurrentYaMarketProductCardInterface $marketProductsCard,
        private MessageDispatchInterface $messageDispatch,
        private YaMarketTokensByProfileInterface $YaMarketTokensByProfile,
        private ProductTotalInOrdersInterface $ProductTotalInOrders,
    ) {}

    /**
     * Обновляем остатки товаров Yandex Market
     */
    public function __invoke(YaMarketProductsStocksMessage $message): void
    {
        /** Получаем все токены профиля */

        $tokensByProfile = $this->YaMarketTokensByProfile
            ->findAll($message->getProfile());

        if(false === $tokensByProfile || false === $tokensByProfile->valid())
        {
            return;
        }

        $CurrentYaMarketProductCardResult = $this->marketProductsCard
            ->forProduct($message->getProduct())
            ->forOfferConst($message->getOfferConst())
            ->forVariationConst($message->getVariationConst())
            ->forModificationConst($message->getModificationConst())
            ->forProfile($message->getProfile())
            ->find();

        if(false === ($CurrentYaMarketProductCardResult instanceof CurrentYaMarketProductCardResult))
        {
            return;
        }

        /**
         * Не обновляем базовую стоимость карточки без обязательных параметров
         */
        if(false === $CurrentYaMarketProductCardResult->isCredentials())
        {
            return;
        }

        /**  Остаток товара в карточке либо если подключен модуль складского учета - остаток на складе */
        $ProductQuantity = $CurrentYaMarketProductCardResult->getProductQuantity();

        /** Если подключен модуль складского учета - расчет согласно необработанных заказов */
        if(class_exists(BaksDevProductsStocksBundle::class))
        {
            /** Получаем количество необработанных заказов */
            $unprocessed = $this->ProductTotalInOrders
                ->onProfile($message->getProfile())
                ->onProduct($message->getProduct())
                ->onOfferConst($message->getOfferConst())
                ->onVariationConst($message->getVariationConst())
                ->onModificationConst($message->getModificationConst())
                ->findTotal();

            $ProductQuantity -= $unprocessed;
        }

        $ProductQuantity = max($ProductQuantity, 0);

        foreach($tokensByProfile as $YaMarketTokenUid)
        {
            /** Возвращает данные об остатках товаров на маркетплейсе */
            $ProductStocksYandexMarket = $this->marketProductStocksGetRequest
                ->forTokenIdentifier($YaMarketTokenUid)
                ->article($CurrentYaMarketProductCardResult->getArticle())
                ->find();

            if(false === $ProductStocksYandexMarket)
            {
                $this->messageDispatch->dispatch(
                    message: $message,
                    stamps: [new MessageDelay('5 seconds')],
                    transport: $message->getProfile().'-low',
                );

                $this->logger->critical(sprintf(
                    'Пробуем обновить остатки артикула %s через 5 секунд',
                    $CurrentYaMarketProductCardResult->getArticle(),
                ));

                continue;
            }

            /**
             * TRUE - возвращается в случае если продажи остановлены, следовательно, не сверяем остатки, а всегда обнуляем
             *
             * @see UpdateYaMarketProductStocksRequest:79
             */
            if($ProductStocksYandexMarket !== true && $ProductStocksYandexMarket === $ProductQuantity)
            {
                $this->logger->info(sprintf(
                    'Наличие соответствует %s: %s == %s',
                    $CurrentYaMarketProductCardResult->getArticle(),
                    $ProductStocksYandexMarket,
                    $ProductQuantity,
                ), [$YaMarketTokenUid]);

                continue;
            }

            /** Обновляем остатки товара если наличие изменилось */
            $this->marketProductStocksUpdateRequest
                ->forTokenIdentifier($YaMarketTokenUid)
                ->article($CurrentYaMarketProductCardResult->getArticle())
                ->total($ProductQuantity)
                ->update();

            $this->logger->info(sprintf(
                'Обновили наличие %s: => %s',
                $CurrentYaMarketProductCardResult->getArticle(),
                $ProductQuantity,
            ), [$YaMarketTokenUid]);

        }
    }
}
