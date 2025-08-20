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

namespace BaksDev\Yandex\Market\Products\Api\Products\Card;

use BaksDev\Yandex\Market\Api\YandexMarket;

final class YaMarketProductUpdateCardRequest extends YandexMarket
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
    public function update(array $card): bool|null
    {
        if($this->isExecuteEnvironment() === false)
        {
            $this->logger->critical('Запрос может быть выполнен только в PROD окружении', [self::class.':'.__LINE__]);
            return true;
        }

        if($this->isCard() === false)
        {
            return true;
        }

        $response = $this->TokenHttpClient()
            ->request(
                'POST',
                sprintf('/businesses/%s/offer-mappings/update', $this->getBusiness()),
                ['json' => ['offerMappings' => [['offer' => $card]]]],
            );

        /** Превышено ограничение на доступ к ресурсу. */
        if($response->getStatusCode() === 420)
        {
            return null;
        }

        $content = $response->toArray(false);

        if($response->getStatusCode() !== 200)
        {
            foreach($content['errors'] as $error)
            {
                $this->logger->critical(sprintf('yandex-market-products: %s (%s)',
                    $error['code'],
                    $error['message']),
                    [self::class.':'.__LINE__, $card]);
            }

            return false;
        }

        if(isset($content['results']['errors']))
        {
            foreach($content['results']['errors'] as $error)
            {
                $this->logger->critical('yandex-market-products: Ошибка обновления карточки ', [self::class.':'.__LINE__, $error]);
            }

            return false;
        }

        return true;
    }
}
