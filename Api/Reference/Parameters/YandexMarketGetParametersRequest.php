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

namespace BaksDev\Yandex\Market\Products\Api\Reference\Parameters;

use BaksDev\Yandex\Market\Api\YandexMarket;
use BaksDev\Yandex\Market\Repository\YaMarketToken\YaMarketTokenByProfileInterface;
use DateInterval;
use DomainException;
use Generator;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Списки характеристик товаров по категориям
 */
final class YandexMarketGetParametersRequest extends YandexMarket
{
    private ?int $category = null;

    public function category(int|string $category): self
    {
        if(is_string($category))
        {
            $category = (int) $category;
        }

        $this->category = $category;

        return $this;
    }


    /**
     * Возвращает список характеристик с допустимыми значениями для заданной категории.
     *
     * ОГРАНИЧЕНИЕ! 50 категорий в минуту
     *
     * @see https://yandex.ru/dev/market/partner-api/doc/ru/reference/content/getCategoryContentParameters
     *
     */
    public function findAll(): Generator
    {
        if(!$this->category)
        {
            throw new InvalidArgumentException('Invalid Argument Category');
        }

        $cache = $this->getCacheInit('yandex-market-products');

        $content = $cache->get('ya-market-parameters-'.$this->category, function(ItemInterface $item) {

            $item->expiresAfter(DateInterval::createFromDateString('1 day'));

            $response = $this->TokenHttpClient()
                ->request(
                    'POST',
                    sprintf('/category/%s/parameters', $this->category),
                );

            $content = $response->toArray(false);

            if($response->getStatusCode() !== 200)
            {
                foreach($content['errors'] as $error)
                {
                    $this->logger->critical($error['code'].': '.$error['message'], [self::class.':'.__LINE__]);
                }

                throw new DomainException(
                    message: 'Ошибка YandexMarketCategoryRequest',
                    code: $response->getStatusCode()
                );
            }

            return $content['result']['parameters'];

        });

        foreach($content as $data)
        {
            yield new YandexMarketParametersDTO($data);
        }
    }
}
