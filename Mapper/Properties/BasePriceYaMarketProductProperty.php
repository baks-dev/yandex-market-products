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

namespace BaksDev\Yandex\Market\Products\Mapper\Properties;

use BaksDev\Core\Cache\AppCacheInterface;
use BaksDev\Core\Lock\AppLockInterface;
use BaksDev\Reference\Currency\Type\Currency;
use BaksDev\Reference\Money\Type\Money;
use BaksDev\Yandex\Market\Products\Api\Tariffs\YandexMarketCalculatorRequest;
use BaksDev\Yandex\Market\Products\Repository\Card\CurrentYaMarketProductsCard\YaMarketProductsCardInterface;
use BaksDev\Yandex\Market\Products\Type\Card\Id\YaMarketProductsCardUid;
use BaksDev\Yandex\Market\Products\Mapper\Properties\Collection\YaMarketProductPropertyInterface;
use DateInterval;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Contracts\Cache\ItemInterface;

#[AutoconfigureTag('baks.ya.product.property')]
final class BasePriceYaMarketProductProperty implements YaMarketProductPropertyInterface
{
    /**
     * Цена на товар.
     * @see https://yandex.ru/dev/market/partner-api/doc/ru/reference/business-assortment/updateOfferMappings#basepricedto
     */
    public const PARAM = 'basicPrice';

    public function __construct(
        private readonly ?YaMarketProductsCardInterface $yaMarketProductsCard = null,
        private readonly ?YandexMarketCalculatorRequest $marketCalculatorRequest = null,
        private readonly ?AppLockInterface $appLock = null,
        private readonly ?AppCacheInterface $appCache = null,
    ) {}

    public function getIndex(): string
    {
        return self::PARAM;
    }

    public function required(): bool
    {
        return true;
    }

    public function default(): ?string
    {
        return null;
    }

    public function choices(): ?array
    {
        return null;
    }

    /**
     * Сортировка (чем меньше число - тем первым в итерации будет значение)
     */
    public static function priority(): int
    {
        return 600;
    }

    /**
     * Проверяет, относится ли статус к данному объекту
     */
    public static function equals(string $status): bool
    {
        return self::PARAM === $status;
    }

    public function isSetting(): bool
    {
        return false;
    }

    public function isCard(): bool
    {
        return false;
    }

    public function getData(array $data): mixed
    {

        /** Не обновляем базовую стоимость карточки без цены */
        if(empty($data['product_price']))
        {
            return null;
        }

        if(
            empty($data['width']) ||
            empty($data['height']) ||
            empty($data['length']) ||
            empty($data['weight'])
        ) {
            return null;
        }

        $Money = new Money($data['product_price'], true);


        /** Кешируем на сутки результат калькуляции услуг с одинаковыми параметрами */
        $cache = $this->appCache->init('yandex-market-products');

        $cacheKey = implode('', [
            $data['profile'],
            $data['market_category'],
            $data['product_price'],
            $data['width'],
            $data['height'],
            $data['length'],
            $data['weight'],
        ]);


        /** Добавляем к стоимости товара стоимость услуг YaMarket */
        $marketCalculator = $cache->get($cacheKey, function (ItemInterface $item) use ($data, $Money): float {

            /** Лимит: 100 запросов в минуту, добавляем лок */
            $this->appLock
                ->createLock([$data['profile'], self::class])
                ->lifetime((60 / 90))
                ->waitAllTime();

            sleep(1);

            $item->expiresAfter(DateInterval::createFromDateString('1 day'));

            /** Добавляем к стоимости товара стоимость услуг YaMarket */
            return $this->marketCalculatorRequest
                ->profile($data['profile'])
                ->category($data['market_category'])
                ->price($Money)
                ->width(($data['width'] / 10))
                ->height(($data['height'] / 10))
                ->length(($data['length'] / 10))
                ->weight(($data['weight'] / 100))
                ->calc();
        });

        $Price = new Money($marketCalculator);
        $Currency = new Currency($data['product_currency']);

        return [
            'value' => $Price->getOnlyPositive(),
            'currencyId' => $Currency->getCurrencyValueUpper()
        ];

    }
}
