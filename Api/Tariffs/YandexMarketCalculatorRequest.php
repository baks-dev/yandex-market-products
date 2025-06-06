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

namespace BaksDev\Yandex\Market\Products\Api\Tariffs;

use BaksDev\Reference\Money\Type\Money;
use BaksDev\Yandex\Market\Api\YandexMarket;
use BaksDev\Yandex\Market\Products\Api\AllShops\YandexMarketShopDTO;
use DateInterval;
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
    private Money $price;

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
        if(false === ($price instanceof Money))
        {
            $price = new Money($price);
        }

        $this->price = $price;

        return $this;
    }

    public function length(int|float $length): self
    {
        $this->length = $length;
        return $this;
    }

    public function width(int|float $width): self
    {
        $this->width = (int) $width;
        return $this;
    }

    public function height(int|float $height): self
    {
        $this->height = (int) $height;
        return $this;
    }

    public function weight(int|float $weight): self
    {
        $this->weight = (int) $weight;
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
    public function calc(): Money|false
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

        $key = md5(
            $this->getCompany().
            $this->category.
            $this->price.
            $this->length.
            $this->width.
            $this->height.
            $this->weight.
            self::class
        );

        $cache = new FilesystemAdapter();

        $content = $cache->get($key, function(ItemInterface $item): array|false {

            $item->expiresAfter(DateInterval::createFromDateString('1 day'));

            /** Присваиваем торговую наценку для расчета стоимости услуг */
            $trade = $this->getPercent();
            $this->price->applyString($trade);

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
                                    "price" => $this->price->getValue(),
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
                $item->expiresAfter(DateInterval::createFromDateString('50 seconds'));

                $this->logger->critical(
                    'yandex-market-products: Ошибка при получении стоимости услуг',
                    [$content, self::class.':'.__LINE__]
                );

                return false;
            }

            return $content;
        });

        if(false === $content)
        {
            return false;
        }

        /** Суммируем все затраты (услуги)  */
        $tariffs = current($content['result']['offers'])['tariffs'];

        $totalAmount = array_reduce($tariffs, static function($carry, $item) {
            return $item['amount'] + $carry;
        }, 0);


        /** Суммируем стоимость товара и услуг */
        $totalAmount = new Money($totalAmount);
        $this->price->add($totalAmount);


        return $this->price;
    }
}
