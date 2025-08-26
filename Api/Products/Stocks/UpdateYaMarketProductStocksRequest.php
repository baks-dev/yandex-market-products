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
use DomainException;
use InvalidArgumentException;

final class
UpdateYaMarketProductStocksRequest extends YandexMarket
{
    private const bool STOP_SALES = false;

    private ?string $article = null;

    private int $total = 0;

    public function article(string $article): self
    {
        $this->article = $article;
        return $this;
    }

    public function total(int $total): self
    {
        $this->total = $total;
        return $this;
    }

    /**
     * Передает данные об остатках товаров на витрине.
     *
     * Лимит: 100 000 товаров в минуту, не более 500 товаров в одном запросе
     *
     * @see https://yandex.ru/dev/market/partner-api/doc/ru/reference/stocks/updateStocks
     *
     */
    public function update(): bool
    {
        if($this->isExecuteEnvironment() === false)
        {
            $this->logger->critical('Запрос может быть выполнен только в PROD окружении', [self::class.':'.__LINE__]);
            return true;
        }

        if(empty($this->article))
        {
            throw new InvalidArgumentException('Invalid Argument article');
        }

        /**
         * Если продажи отключены - всегда обнуляем остаток
         *
         * @see YaMarketProductsStocksUpdate:110
         *
         */
        if($this->isStocks() === false)
        {
            $this->total = 0;
            $this->logger->warning(sprintf(' %s: Останавливаем продажи артикула', $this->article));
        }

        $response = $this->TokenHttpClient()
            ->request(
                'PUT',
                sprintf('/campaigns/%s/offers/stocks', $this->getCompany()),
                [
                    'json' => [
                        'skus' => [
                            [
                                'sku' => $this->article,
                                "items" => [
                                    ["count" => max($this->total, 0)],
                                ],
                            ],
                        ],
                    ],
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
                code: $response->getStatusCode(),
            );
        }

        return true;

    }

}
