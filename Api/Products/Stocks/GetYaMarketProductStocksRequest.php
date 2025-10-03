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

namespace BaksDev\Yandex\Market\Products\Api\Products\Stocks;

use BaksDev\Yandex\Market\Api\YandexMarket;
use DateInterval;
use InvalidArgumentException;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Информация об остатках и оборачиваемости
 */
final class GetYaMarketProductStocksRequest extends YandexMarket
{
    private int $total = 0;

    private int $reserve = 0;

    private ?string $article = null;

    public function article(string $article): self
    {
        /** Обнуляем остатки и зарезервированные */
        $this->total = 0;
        $this->reserve = 0;

        /** Присваиваем артикул */
        $this->article = $article;
        return $this;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getReserve(): int
    {
        return $this->reserve;
    }

    /**
     *
     * Возвращает данные об остатках товаров (для всех моделей) и об оборачиваемости товаров (для модели FBY).
     *
     * Лимит: 100 000 товаров в минуту
     *
     * @see https://yandex.ru/dev/market/partner-api/doc/ru/reference/stocks/getStocks
     *
     */
    public function find(): self|bool
    {
        if(empty($this->article))
        {
            throw new InvalidArgumentException('Invalid Argument article');
        }

        /**
         * Если продажи отключены - всегда обнуляем остаток возвращая TRUE
         */
        if($this->isStocks() === false)
        {
            //return true;
        }

        $cache = $this->getCacheInit('yandex-market-products');

        $response = $cache->get($this->getCompany().$this->article, function(ItemInterface $item) {

            $item->expiresAfter(DateInterval::createFromDateString('5 seconds'));

            return $this->TokenHttpClient()
                ->request(
                    'POST',
                    sprintf('/campaigns/%s/offers/stocks', $this->getCompany()),
                    ['json' =>
                        ['offerIds' => [$this->article]],
                    ],
                );
        });


        $content = $response->toArray(false);

        if($response->getStatusCode() !== 200)
        {
            foreach($content['errors'] as $error)
            {
                $this->logger->critical($error['code'].': '.$error['message'], [self::class.':'.__LINE__]);
            }

            return false;
        }

        $warehouses = current($content['result']['warehouses']);

        if(empty($warehouses))
        {
            $this->total = 0;
            $this->reserve = 0;

            return $this;
        }

        $stocks = current($warehouses['offers'])['stocks'];

        if(empty($stocks))
        {
            $this->total = 0;
            $this->reserve = 0;

            return $this;
        }

        /**
         * Получаем остаток «Доступный к заказу»
         */
        $available = array_filter($stocks, static function($v) {
            return $v['type'] === 'AVAILABLE';
        });

        $this->total = empty($available) ? 0 : (int) current($available)['count'];


        /**
         * Получаем зарезервированный остаток
         */
        $freeze = array_filter($stocks, static function($v) {
            return $v['type'] === 'FREEZE';
        });

        $this->reserve = empty($freeze) ? 0 : (int) current($freeze)['count'];


        return $this;
    }
}
