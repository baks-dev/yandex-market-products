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

namespace BaksDev\Yandex\Market\Products\Messenger\YaMarketProductsPriceUpdate;

use BaksDev\Core\Cache\AppCacheInterface;
use BaksDev\Core\Lock\AppLockInterface;
use BaksDev\Reference\Currency\Type\Currency;
use BaksDev\Reference\Money\Type\Money;
use BaksDev\Yandex\Market\Products\Api\Products\Card\YandexMarketProductDTO;
use BaksDev\Yandex\Market\Products\Api\Products\Card\FindProductYandexMarketRequest;
use BaksDev\Yandex\Market\Products\Api\Products\Price\YandexMarketProductPriceUpdateRequest;
use BaksDev\Yandex\Market\Products\Api\Tariffs\YandexMarketCalculatorRequest;
use BaksDev\Yandex\Market\Products\Repository\Card\CurrentYaMarketProductsCard\YaMarketProductsCardInterface;
use BaksDev\Yandex\Market\Products\Repository\Card\CurrentYaMarketProductsStocks\YaMarketProductsStocksInterface;
use DateInterval;
use DomainException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\Cache\ItemInterface;

#[AsMessageHandler]
final class YaMarketProductsPriceUpdate
{
    private LoggerInterface $logger;

    public function __construct(
        private readonly YandexMarketCalculatorRequest $marketCalculatorRequest,
        private readonly FindProductYandexMarketRequest $yandexMarketProductRequest,
        private readonly YandexMarketProductPriceUpdateRequest $marketProductPriceRequest,
        private readonly YaMarketProductsCardInterface $marketProductsCard,
        private readonly AppLockInterface $appLock,
        private readonly AppCacheInterface $appCache,
        LoggerInterface $yandexMarketProductsLogger,
    ) {
        $this->logger = $yandexMarketProductsLogger;
    }

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
        ) {
            $this->logger->critical(
                sprintf('Параметры упаковки товара %s не найдены! Не обновляем базовую стоимость товара YaMarket', $Card['article']),
                [self::class.':'.__LINE__]
            );

            return;
        }


        /** Кешируем на сутки результат калькуляции услуг с одинаковыми параметрами */
        $cache = $this->appCache->init('yandex-market-products');

        $cacheKey = implode('', [
            $message->getProfile(),
            $Card['market_category'],
            $Card['product_price'],
            $Card['width'],
            $Card['height'],
            $Card['length'],
            $Card['weight'],
        ]);

        $Money = new Money($Card['product_price'], true);
        $Currency = new Currency($Card['product_currency']);

        $marketCalculator = $cache->get($cacheKey, function (ItemInterface $item) use ($Card, $Money, $message): float {

            /** Лимит: 100 запросов в минуту, добавляем лок */
            $this->appLock
                ->createLock([$message->getProfile(), self::class])
                ->lifetime((60 / 90))
                ->waitAllTime();

            $item->expiresAfter(DateInterval::createFromDateString('1 day'));

            /** Добавляем к стоимости товара стоимость услуг YaMarket */
            return $this->marketCalculatorRequest
                ->profile($message->getProfile())
                ->category($Card['market_category'])
                ->price($Money)
                ->width(($Card['width'] / 10))
                ->height(($Card['height'] / 10))
                ->length(($Card['length'] / 10))
                ->weight(($Card['weight'] / 100))
                ->calc();
        });


        /**
         * Проверяем изменение цены в карточке если добавлена ранее
         */

        $Price = new Money($marketCalculator);

        $MarketProduct = $this->yandexMarketProductRequest
            ->profile($message->getProfile())
            ->article($Card['article'])
            ->find();

        if($MarketProduct->valid())
        {
            /** @var YandexMarketProductDTO $YandexMarketProductDTO */
            $YandexMarketProductDTO = $MarketProduct->current();

            if(true === $YandexMarketProductDTO->getPrice()->equals($Price))
            {
                return;
            }
        }

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
