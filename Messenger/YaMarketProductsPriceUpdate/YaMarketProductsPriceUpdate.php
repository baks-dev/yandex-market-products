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

use BaksDev\Core\Cache\AppCacheInterface;
use BaksDev\Core\Deduplicator\DeduplicatorInterface;
use BaksDev\Core\Lock\AppLockInterface;
use BaksDev\Core\Messenger\MessageDelay;
use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Reference\Currency\Type\Currency;
use BaksDev\Reference\Money\Type\Money;
use BaksDev\Yandex\Market\Products\Api\Products\Card\Find\YaMarketProductDTO;
use BaksDev\Yandex\Market\Products\Api\Products\Card\Find\YaMarketProductFindCardRequest;
use BaksDev\Yandex\Market\Products\Api\Products\Price\YaMarketProductUpdatePriceRequest;
use BaksDev\Yandex\Market\Products\Api\Tariffs\YandexMarketCalculatorRequest;
use BaksDev\Yandex\Market\Products\Repository\Card\CurrentYaMarketProductsCard\YaMarketProductsCardInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class YaMarketProductsPriceUpdate
{
    public function __construct(
        #[Target('yandexMarketProductsLogger')] private LoggerInterface $logger,
        private YandexMarketCalculatorRequest $marketCalculatorRequest,
        private YaMarketProductFindCardRequest $yandexMarketProductRequest,
        private YaMarketProductUpdatePriceRequest $marketProductPriceRequest,
        private YaMarketProductsCardInterface $marketProductsCard,
        private AppLockInterface $appLock,
        private AppCacheInterface $appCache,
        private MessageDispatchInterface $messageDispatch,
        private DeduplicatorInterface $deduplicator,
    ) {}

    /**
     * Обновляем базовую цену товара на Yandex Market
     */
    public function __invoke(YaMarketProductsPriceMessage $message): void
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

        /** Не обновляем базовую стоимость карточки без цены */
        if(empty($Card['product_price']))
        {
            return;
        }

        if(
            empty($Card['width']) ||
            empty($Card['height']) ||
            empty($Card['length']) ||
            empty($Card['weight'])
        )
        {
            $this->logger->warning(
                sprintf('Параметры упаковки товара %s не найдены!', $Card['article']),
                [self::class.':'.__LINE__]
            );

            return;
        }


        $Deduplicator = $this->deduplicator
            ->namespace('yandex-market-products')
            ->expiresAfter('1 day')
            ->deduplication([
                $message->getProfile(),
                $Card,
                self::class
            ]);

        if($Deduplicator->isExecuted())
        {
            return;
        }

        $Deduplicator->save();

        /**
         * Делаем расчет стоимости реализации товара
         * Стоимости товара + Стоимость услуг YaMarket + Торговая наценка
         */

        /** Лимит: 100 запросов в минуту, добавляем лок */
        $this->appLock
            ->createLock([$message->getProfile(), self::class])
            ->lifetime((60 / 90))
            ->waitAllTime();


        /**
         * Добавляем к стоимости товара стоимость услуг YaMarket
         * + Торговая наценка (%)
         */

        $Money = new Money($Card['product_price'], true);

        $Price = $this->marketCalculatorRequest
            ->profile($message->getProfile())
            ->category($Card['market_category'])
            ->price($Money)
            ->width(($Card['width'] / 10))
            ->height(($Card['height'] / 10))
            ->length(($Card['length'] / 10))
            ->weight(($Card['weight'] / 100))
            ->calc();


        if(false === $Price)
        {
            $this->messageDispatch
                ->dispatch(
                    $message,
                    stamps: [new MessageDelay('1 minutes')]
                );

            $this->logger->critical(
                sprintf('yandex-market-products: Пробуем обновит стоимость %s через 1 минуту', $Card['article']),
                [self::class.':'.__LINE__]
            );

            return;
        }


        /**
         * Проверяем изменение цены в карточке если добавлена ранее
         */

        $MarketProduct = $this->yandexMarketProductRequest
            ->profile($message->getProfile())
            ->article($Card['article'])
            ->find();


        if($MarketProduct->valid())
        {
            /** @var YaMarketProductDTO $YandexMarketProductDTO */
            $YandexMarketProductDTO = $MarketProduct->current();

            if(true === $YandexMarketProductDTO->getPrice()->equals($Price))
            {
                return;
            }
        }


        $Currency = new Currency($Card['product_currency']);

        $this->marketProductPriceRequest
            ->profile($message->getProfile())
            ->article($Card['article'])
            ->price($Price)
            ->currency($Currency)
            ->update();

        $this->logger->info(
            sprintf(
                'Обновили стоимость товара %s (%s) : %s => %s',
                $Card['article'],
                $Money->getValue(), // стоимость в карточке
                $Price->getValue(), // новая стоимость на маркетплейс
                $Currency->getCurrencyValueUpper()
            ),
            [self::class.':'.__LINE__]
        );

    }
}
