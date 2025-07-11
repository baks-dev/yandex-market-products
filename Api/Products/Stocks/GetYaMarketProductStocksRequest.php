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
    private ?string $article = null;

    public function article(string $article): self
    {
        $this->article = $article;
        return $this;
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
    public function find(): int|bool
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
            return true;
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
            return 0;
        }

        $stocks = current($warehouses['offers'])['stocks'];

        if(empty($stocks))
        {
            return 0;
        }

        /** Получаем остаток «Доступный к заказу» */
        $available = array_filter($stocks, static function($v) {
            return $v['type'] === 'AVAILABLE';
        });

        if(empty($available))
        {
            return 0;
        }

        $available = current($available);

        return $available['count'];

    }
}
