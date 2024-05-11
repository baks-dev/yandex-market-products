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

namespace BaksDev\Yandex\Market\Products\Messenger\Card;

use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Yandex\Market\Products\Api\Products\Card\YandexMarketProductDTO;
use BaksDev\Yandex\Market\Products\Api\Products\Card\YandexMarketProductRequest;
use BaksDev\Yandex\Market\Products\Api\Products\Update\YandexMarketProductUpdateRequest;
use BaksDev\Yandex\Market\Products\Repository\Card\CurrentYaMarketProductsCard\YaMarketProductsCardInterface;
use BaksDev\Yandex\Market\Products\Type\Settings\Property\Properties\Collection\YaMarketProductPropertyInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Uid\UuidV7;

#[AsMessageHandler(priority: 999)]
final class YaMarketProductsCardUpdate
{

    /** Свойства карточки товара */
    private iterable $property;

    private YandexMarketProductUpdateRequest $marketProductUpdate;

    private LoggerInterface $logger;
    private YaMarketProductsCardInterface $marketProductsCard;
    private YandexMarketProductRequest $yandexMarketProductRequest;

    public function __construct(
        #[TaggedIterator('baks.ya.product.property', defaultPriorityMethod: 'priority')] iterable $property,
        YandexMarketProductRequest $yandexMarketProductRequest,
        YandexMarketProductUpdateRequest $marketProductUpdate,
        YaMarketProductsCardInterface $marketProductsCard,
        LoggerInterface $yandexMarketProductsLogger
    )
    {
        $this->marketProductUpdate = $marketProductUpdate;
        $this->property = $property;
        $this->logger = $yandexMarketProductsLogger;
        $this->marketProductsCard = $marketProductsCard;
        $this->yandexMarketProductRequest = $yandexMarketProductRequest;
    }

    /**
     * Добавляем (обновляем) карточку товара на Yandex Market
     */
    public function __invoke(YaMarketProductsCardMessage $message): void
    {
        $Card = $this->marketProductsCard->findByCard($message->getId());

        if(!$Card)
        {
            return;
        }

        /** Не добавляем карточку без цены */
        if(empty($Card['product_price']))
        {
            $this->logger->warning(sprintf('Не добавляем карточку %s без цены', $Card['article']));
            return;
        }

        $request = null;

        /** @var YaMarketProductPropertyInterface $item */
        foreach($this->property as $item)
        {
            //$value = $item->getData($message->getId());
            $value = $item->getData($Card);

            if($value === null)
            {
                continue;
            }

            $request[$item->getValue()] = $item->getData($message->getId());
        }

        if(!$request)
        {
            return;
        }

        /** Карточка товара YaMarket */
        $MarketProduct = $this->yandexMarketProductRequest
            ->profile($Card['profile'])
            ->article($Card['article'])
            ->find();

        /** Если карточка имеется - проверяем изменения */
        if($MarketProduct->valid())
        {
            /** @var YandexMarketProductDTO $YandexMarketProductDTO */

            $YandexMarketProductDTO = $MarketProduct->current();

            /** Не обновляем карточку если нет изменений (фото параметров) */
            if($YandexMarketProductDTO->equals($request) === true)
            {
                $this->logger->info(sprintf('Изменения в карточке %s отсутствуют', $request['offerId']));
                return;
            }
        }

        $this->marketProductUpdate
            ->profile($Card['profile'])
            ->update($request);

        $this->logger->info(sprintf('Обновили карточку товара %s', $request['offerId']));

    }
}