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

namespace BaksDev\Yandex\Market\Products\Api\Products\Update;

use BaksDev\Yandex\Market\Api\YandexMarket;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

final class YandexMarketProductUpdateRequest extends YandexMarket
{

    /**
     * Добавляет товары в каталог или редактирует информацию об уже имеющихся товарах.
     *
     * Для новых товаров обязательно укажите параметры:
     * - offerId (SKU)
     * - name
     * - category
     * - pictures
     * - vendor
     * - description
     *
     * @see https://yandex.ru/dev/market/partner-api/doc/ru/reference/business-assortment/updateOfferMappings
     *
     */
    public function update(array $card)
    {
        $response = $this->TokenHttpClient()
            ->request(
                'POST',
                sprintf('/businesses/%s/offer-mappings/update', $this->getBusiness()),
                ['json' => ['offerMappings' => [['offer' => $card]]]],
            );

        $content = $response->toArray(false);

        if($response->getStatusCode() !== 200)
        {
            foreach($content['errors'] as $error)
            {
                $this->logger->critical($error['code'].': '.$error['message'], [__FILE__.':'.__LINE__]);
            }

            throw new \DomainException(
                message: 'Ошибка '.self::class,
                code: $response->getStatusCode()
            );
        }

        /** Удаляем файловый кеш карточки */
        $cache = new FilesystemAdapter('yandex-market');
        $cache->clear();

        return $response->toArray(false);

    }

}
