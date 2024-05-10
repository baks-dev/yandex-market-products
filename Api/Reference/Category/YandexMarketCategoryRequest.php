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

namespace BaksDev\Yandex\Market\Products\Api\Reference\Category;

use BaksDev\Yandex\Market\Api\YandexMarket;
use DomainException;
use Generator;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

final class YandexMarketCategoryRequest extends YandexMarket
{
    /**
     * Возвращает дерево категорий Маркета.
     *
     * ОГРАНИЧЕНИЕ! 1 000 запросов в час
     *
     * @see https://yandex.ru/dev/market/partner-api/doc/ru/reference/categories/getCategoriesTree
     *
     */
    public function findAll() //: Generator
    {
        $cache = new FilesystemAdapter('yandex-market');

        $content = $cache->get('ya-market-categories', function(
            ItemInterface $item
        ) {

            $item->expiresAfter(\DateInterval::createFromDateString('1 day'));

            $response = $this->TokenHttpClient()
                ->request(
                    'POST',
                    '/categories/tree',
                );

            $content = $response->toArray(false);

            if($response->getStatusCode() !== 200)
            {
                foreach($content as $error)
                {
                    $this->logger->critical($error['code'].': '.$error['message'], [__FILE__.':'.__LINE__]);
                }

                throw new DomainException(
                    message: 'Ошибка YandexMarketCategoryRequest',
                    code: $response->getStatusCode()
                );
            }

            return $this->processArray($content['result']['children']);

        });

        foreach($content as $data)
        {
            yield new YandexMarketCategoryDTO($data);
        }
    }

    private function processArray($array, string $parent = null) {

        $result = [];

        foreach ($array as $item) {

            $hasChildren = isset($item["children"]) && is_array($item["children"]) && count($item["children"]) > 0;

            $name = $parent ? $parent . ' - ' . $item["name"] : $item["name"];

            if (!$hasChildren) {

                $result[] = [
                    "id" => $item["id"],
                    "name" => $name
                ];

            } else {
                $result = array_merge($result, $this->processArray($item["children"], $name));
            }
        }

        return $result;
    }
}