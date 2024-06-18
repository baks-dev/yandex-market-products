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

namespace BaksDev\Yandex\Market\Products\Messenger\Card\YaMarketProductsPriceUpdate;

use BaksDev\Core\Lock\AppLockInterface;
use BaksDev\Reference\Currency\Type\Currency;
use BaksDev\Reference\Money\Type\Money;
use BaksDev\Yandex\Market\Products\Api\Products\Card\YandexMarketProductDTO;
use BaksDev\Yandex\Market\Products\Api\Products\Card\YandexMarketProductRequest;
use BaksDev\Yandex\Market\Products\Api\Products\Price\YandexMarketProductPriceUpdateRequest;
use BaksDev\Yandex\Market\Products\Api\Tariffs\YandexMarketCalculatorRequest;
use BaksDev\Yandex\Market\Products\Repository\Card\CurrentYaMarketProductsCard\YaMarketProductsCardInterface;
use DomainException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class YaMarketProductsPriceUpdate
{
    private YaMarketProductsCardInterface $marketProductsCard;
    private YandexMarketProductPriceUpdateRequest $marketProductPriceRequest;
    private LoggerInterface $logger;
    private YandexMarketCalculatorRequest $marketCalculatorRequest;
    private YandexMarketProductRequest $yandexMarketProductRequest;
    private AppLockInterface $appLock;

    public function __construct(
        YandexMarketCalculatorRequest $marketCalculatorRequest,
        YandexMarketProductRequest $yandexMarketProductRequest,
        YandexMarketProductPriceUpdateRequest $marketProductPriceRequest,
        YaMarketProductsCardInterface $marketProductsCard,
        LoggerInterface $yandexMarketProductsLogger,
        AppLockInterface $appLock
    ) {

        $this->marketProductsCard = $marketProductsCard;
        $this->logger = $yandexMarketProductsLogger;
        $this->marketProductPriceRequest = $marketProductPriceRequest;
        $this->marketCalculatorRequest = $marketCalculatorRequest;
        $this->yandexMarketProductRequest = $yandexMarketProductRequest;
        $this->appLock = $appLock;
    }

    /**
     * Обновляем базовую цену товара на Yandex Market
     */
    public function __invoke(YaMarketProductsPriceMessage $message): void
    {

        $Card = $this->marketProductsCard->findByCard($message->getId());

        if(!$Card)
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
                [__FILE__.':'.__LINE__]
            );

            return;
        }

        $MarketProduct = $this->yandexMarketProductRequest
            ->profile($Card['profile'])
            ->article($Card['article'])
            ->find();

        /** Если карточка новая - понадобится время на обновление цены */
        if(!$MarketProduct->valid())
        {
            $this->logger->critical(
                sprintf('Не обновляем базовую стоимость товара %s: карточка товара YaMarket не найдена', $Card['article']),
                [__FILE__.':'.__LINE__]
            );

            throw new DomainException('Карточка товара YaMarket не найдена');
        }


        $Money = new Money($Card['product_price'] / 100);
        $Currency = new Currency($Card['product_currency']);


        /** Лимит: 100 запросов в минуту, добавляем лок на 0.6 сек */
        $lock = $this->appLock
            ->createLock(md5($Card['profile'].self::class))
            ->lifetime(0.6)
            ->waitAllTime();

        /** Добавляем к стоимости товара стоимость услуг YaMarket */
        $marketCalculator = $this->marketCalculatorRequest
            ->profile($Card['profile'])
            ->category($Card['market_category'])
            ->price($Money)
            ->width(($Card['width'] / 10))
            ->height(($Card['height'] / 10))
            ->length(($Card['length'] / 10))
            ->weight(($Card['weight'] / 100))
            ->calc();

        $lock->release();


        $Price = new Money($marketCalculator);


        /** @var YandexMarketProductDTO $YandexMarketProductDTO */
        $YandexMarketProductDTO = $MarketProduct->current();

        /** Обновляем базовую стоимость товара если цена изменилась */
        if(false === $YandexMarketProductDTO->getPrice()->equals($Price))
        {
            $this->marketProductPriceRequest
                ->profile($Card['profile'])
                ->article($Card['article'])
                ->price($Price)
                ->currency($Currency)
                ->update();

            $this->logger->info(
                sprintf('Обновили базовую стоимость товара %s: %s %s', $Card['article'], $Price->getValue(), $Currency->getCurrencyValueUpper()),
                [__FILE__.':'.__LINE__]
            );

        }
    }
}
