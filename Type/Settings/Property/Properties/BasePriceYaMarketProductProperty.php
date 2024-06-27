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

namespace BaksDev\Yandex\Market\Products\Type\Settings\Property\Properties;

use BaksDev\Reference\Currency\Type\Currency;
use BaksDev\Reference\Money\Type\Money;
use BaksDev\Yandex\Market\Products\Api\Tariffs\YandexMarketCalculatorRequest;
use BaksDev\Yandex\Market\Products\Repository\Card\CurrentYaMarketProductsCard\YaMarketProductsCardInterface;
use BaksDev\Yandex\Market\Products\Type\Card\Id\YaMarketProductsCardUid;
use BaksDev\Yandex\Market\Products\Type\Settings\Property\Properties\Collection\YaMarketProductPropertyInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

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
    ) {}

    public function getValue(): string
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

    public function getData(YaMarketProductsCardUid|array $card): mixed
    {
        if($this->yaMarketProductsCard)
        {
            $Card = $card instanceof YaMarketProductsCardUid ? $this->yaMarketProductsCard->findByCard($card) : $card;

            /** Не обновляем базовую стоимость карточки без цены */
            if(empty($Card['product_price']))
            {
                return null;
            }

            if(
                empty($Card['width']) ||
                empty($Card['height']) ||
                empty($Card['length']) ||
                empty($Card['weight'])
            ) {
                return null;
            }

            $Money = new Money($Card['product_price'] / 100);

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

            $Price = new Money($marketCalculator);
            $Currency = new Currency($Card['product_currency']);

            return [
                'value' => $Price->getOnlyPositive(),
                'currencyId' => $Currency->getCurrencyValueUpper()
            ];
        }

        return null;
    }
}
