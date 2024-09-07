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

use App\Kernel;
use BaksDev\Yandex\Market\Api\YandexMarket;
use DomainException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

final class YandexMarketProductDeleteRequest extends YandexMarket
{
    /**
     * Удаление товаров из каталога
     *
     * Список SKU товаров, которые нужно удалить.
     * - offerIds (SKU)
     *
     * @see https://yandex.ru/dev/market/partner-api/doc/ru/reference/business-assortment/deleteOffers
     *
     */
    public function delete(array|string $articles): bool
    {
        if(Kernel::isTestEnvironment())
        {
            return true;
        }

        $offer = is_array($articles) ?: [$articles];

        $response = $this->TokenHttpClient()
            ->request(
                'POST',
                sprintf('/businesses/%s/offer-mappings/delete', $this->getBusiness()),
                ['json' => ['offerIds' => $offer]],
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

        return true;
    }
}
