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

namespace BaksDev\Yandex\Market\Products\Api\Products\Price;

use BaksDev\Reference\Currency\Type\Currencies\RUR;
use BaksDev\Reference\Currency\Type\Currency;
use BaksDev\Reference\Money\Type\Money;
use BaksDev\Yandex\Market\Api\YandexMarket;
use DateInterval;
use InvalidArgumentException;
use Symfony\Contracts\Cache\ItemInterface;

final class GetYaMarketProductPriceRequest extends YandexMarket
{
    private ?string $article = null;

    public function article(string $article): self
    {
        $this->article = $article;
        return $this;
    }

    /**
     * Просмотр цен на указанные товары в магазине
     *
     * Лимит: 150 000 товаров в минуту
     *
     * @see https://yandex.ru/dev/market/partner-api/doc/ru/reference/assortment/getPricesByOfferIds
     *
     */
    public function find(): int|false
    {
        if(empty($this->article))
        {
            throw new InvalidArgumentException('Invalid Argument Article');
        }

        $cache = $this->getCacheInit('yandex-market-products');
        $key = $this->getCompany().$this->article;
        $cache->deleteItem($key);

        $response = $cache->get($this->getCompany().$this->article, function(ItemInterface $item) {

            $item->expiresAfter(DateInterval::createFromDateString('1 minute'));

            return $this->TokenHttpClient()
                ->request(
                    'POST',
                    sprintf('/campaigns/%s/offer-prices', $this->getCompany()),
                    ['json' =>
                        ['offerIds' => [$this->article]],
                    ],
                );
        });


        $content = $response->toArray(false);

        if($response->getStatusCode() !== 200)
        {

            $this->logger->critical(
                sprintf(
                    'yandex-market-products: Ошибка %s получении стоимости продукта',
                    $response->getStatusCode(),
                ),
                [
                    self::class.':'.__LINE__,
                    $this->getProfile(),
                    $this->getCompany(),
                    $this->article,
                    $content,
                ],
            );

            return false;
        }


        /** Если торговых предложений не найдено - возвращает FALSE */
        if(true === empty($content['result']['offers']))
        {
            return false;
        }

        $prices = array_filter($content['result']['offers'], function($offer) {
            return $offer['offerId'] === $this->article;
        });

        /** Если артикула не найдено - возвращает FALSE */
        if(true === empty($prices))
        {
            return false;
        }

        $price = $prices[array_key_first($prices)];

        /** Если найденному артикулу не присвоена цена - возвращает FALSE */
        if(true === empty($price['price']))
        {
            return false;
        }

        return $price['price']['value'] ?: false;

    }
}