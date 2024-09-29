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

namespace BaksDev\Yandex\Market\Products\Api\Tariffs;

use BaksDev\Reference\Currency\Type\Currencies\RUR;
use BaksDev\Reference\Currency\Type\Currency;
use BaksDev\Reference\Money\Type\Money;
use BaksDev\Yandex\Market\Api\YandexMarket;
use BaksDev\Yandex\Market\Products\Api\AllShops\YandexMarketShopDTO;
use DomainException;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionProperty;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Калькулятор стоимости услуг
 */
final class YandexMarketCalculatorRequest extends YandexMarket
{
    /**
     * Идентификатор категории товара на Маркете.
     */
    private int $category;

    /** Цена товара в рублях. */
    private float $price;

    /** Длина товара в сантиметрах. */
    private int|float $length;


    /** Ширина товара в сантиметрах. */
    private int|float $width;


    /** Высота товара в сантиметрах. */

    private int|float $height;


    /** Вес товара в килограммах. */
    private int|float $weight;


    public function category(int $category): self
    {
        $this->category = $category;
        return $this;
    }

    public function price(Money|int|float $price): self
    {
        if($price instanceof Money)
        {
            $price = $price->getValue();
        }

        $this->price = (float) $price;

        return $this;
    }

    public function length(int|float $length): self
    {
        $this->length = $length;
        return $this;
    }

    public function width(int|float $width): self
    {
        $this->width = $width;
        return $this;
    }

    public function height(int|float $height): self
    {
        $this->height = $height;
        return $this;
    }

    public function weight(int|float $weight): self
    {
        $this->weight = $weight;
        return $this;
    }


    /**
     * Рассчитывает стоимость услуг Маркета для товаров с заданными параметрами.
     * Порядок товаров в запросе и ответе сохраняется, чтобы определить, для какого товара рассчитана стоимость услуги.
     *
     * Лимит: 100 запросов в минуту, не более 200 товаров в одном запросе
     *
     * @see https://yandex.ru/dev/market/partner-api/doc/ru/reference/tariffs/calculateTariffs
     *
     */
    public function calc(): float
    {
        $reflect = new ReflectionClass($this);
        $properties = $reflect->getProperties(ReflectionProperty::IS_PRIVATE);

        /** @var ReflectionProperty $property */
        foreach($properties as $property)
        {
            $name = $property->getName();

            if(empty($this->{$name}))
            {
                $this->logger->critical(sprintf('Необходимо передать обязательный параметр %s', $name), [self::class.':'.__LINE__]);
                throw new InvalidArgumentException(sprintf('Invalid Argument %s', $name));
            }
        }

        $response = $this->TokenHttpClient()
            ->request(
                'POST',
                '/tariffs/calculate',
                ['json' =>
                    [
                        "parameters" => [
                            "campaignId" => $this->getCompany(),
                            "frequency" => "DAILY"
                        ],
                        "offers" => [
                            [
                                "categoryId" => $this->category,
                                "price" => $this->price,
                                "length" => $this->length,
                                "width" => $this->width,
                                "height" => $this->height,
                                "weight" => $this->weight
                            ]
                        ]
                    ]
                ],
            );

        $content = $response->toArray(false);

        if($response->getStatusCode() !== 200)
        {
            foreach($content['errors'] as $error)
            {
                $this->logger->critical($error['code'].': '.$error['message'], [self::class.':'.__LINE__]);
            }

            throw new DomainException(
                message: 'Ошибка '.self::class,
                code: $response->getStatusCode()
            );
        }

        /** Суммируем все затраты (услуги)  */
        $tariffs = current($content['result']['offers'])['tariffs'];

        $totalAmount = array_reduce($tariffs, static function ($carry, $item) {
            return $item['amount'] * 100 + $carry;
        }, 0);

        /** Суммируем стоимость товара и услуг  */
        $price = ($this->price * 100) + $totalAmount;

        /** Наценка стоимости */
        $price += $this->getPercent($price);

        /** Округляем стоимость до десяток */
        $price = ceil($price / 1000) * 10;

        return $price;
    }
}
