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

namespace BaksDev\Yandex\Market\Products\Api\Products\Card;

use BaksDev\Yandex\Market\Api\YandexMarket;
use DomainException;
use Generator;
use InvalidArgumentException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

final class YandexMarketProductRequest extends YandexMarket
{
    private ?string $article = null;

    public function article(string|array $article): self
    {
        $this->article = $article;
        return $this;
    }

    /**
     * Возвращает товар (товары) по артикулу
     *
     * ОГРАНИЧЕНИЕ! 600 запросов в минуту, не более 200 товаров в одном запросе
     *
     * @see https://yandex.ru/dev/market/partner-api/doc/ru/reference/business-assortment/getOfferMappings
     *
     */
    public function find(): Generator
    {
        if(!$this->article)
        {
            throw new InvalidArgumentException('Invalid Argument article');
        }

        $data['offerIds'] = is_array($this->article) ? $this->article : [$this->article];

        $response = $this->TokenHttpClient()
            ->request(
                'POST',
                sprintf('/businesses/%s/offer-mappings', $this->getBusiness()),
                ['json' => $data],
            );

        $content = $response->toArray(false);

        if($response->getStatusCode() !== 200)
        {
            foreach($content as $error)
            {
                $this->logger->critical($error['code'].': '.$error['message'], [__FILE__.':'.__LINE__]);
            }

            throw new DomainException(
                message: 'Ошибка YandexMarketShopRequest',
                code: $response->getStatusCode()
            );
        }


        if(empty($content['result']['offerMappings']))
        {
            return null;
        }

        foreach($content['result']['offerMappings'] as $offer)
        {
            yield new YandexMarketProductDTO($this->getProfile(), $offer['offer']);
        }

    }
}
