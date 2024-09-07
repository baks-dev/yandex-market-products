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

use BaksDev\Core\Lock\AppLockInterface;
use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Yandex\Market\Products\Api\Products\Card\FindProductYandexMarketRequest;
use BaksDev\Yandex\Market\Products\Api\Products\Update\YandexMarketProductUpdateRequest;
use BaksDev\Yandex\Market\Products\Mapper\YandexMarketMapper;
use BaksDev\Yandex\Market\Products\Repository\Card\CurrentYaMarketProductsCard\YaMarketProductsCardInterface;
use BaksDev\Yandex\Market\Products\Mapper\Properties\Collection\YaMarketProductPropertyInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class YaMarketProductsCardUpdate
{
    private LoggerInterface $logger;

    //
    //    /** Свойства карточки товара */
    //    private iterable $property;
    //
    //    private YandexMarketProductUpdateRequest $marketProductUpdate;
    //
    //    private YaMarketProductsCardInterface $marketProductsCard;
    //    private FindProductYandexMarketRequest $yandexMarketProductRequest;
    //    private MessageDispatchInterface $messageDispatch;
    //    private AppLockInterface $appLock;

    public function __construct(
        #[AutowireIterator('baks.ya.product.property', defaultPriorityMethod: 'priority')] private readonly iterable $property,
        private readonly FindProductYandexMarketRequest $yandexMarketProductRequest,
        private readonly YandexMarketProductUpdateRequest $marketProductUpdate,
        private readonly YaMarketProductsCardInterface $marketProductsCard,
        private readonly YandexMarketMapper $yandexMarketMapper,
        private readonly MessageDispatchInterface $messageDispatch,
        private readonly AppLockInterface $appLock,
        LoggerInterface $yandexMarketProductsLogger,
    ) {
        //        $this->marketProductUpdate = $marketProductUpdate;
        //        $this->property = $property;
        //        $this->marketProductsCard = $marketProductsCard;
        //        $this->yandexMarketProductRequest = $yandexMarketProductRequest;
        //        $this->messageDispatch = $messageDispatch;
        //        $this->appLock = $appLock;

        $this->logger = $yandexMarketProductsLogger;
    }

    /**
     * Добавляем (обновляем) карточку товара на Yandex Market
     */
    public function __invoke(YaMarketProductsCardMessage $message): void
    {
        $Card = $this->marketProductsCard
            ->forProduct($message->getProduct())
            ->forOfferConst($message->getOfferConst())
            ->forVariationConst($message->getVariationConst())
            ->forModificationConst($message->getModificationConst())
            ->find();

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

        /** Гидрируем карточку на свойства запроса */
        $request = $this->yandexMarketMapper->getData($Card);

        /** Лимит: 600 запросов в минуту, добавляем лок на 0.6 сек */
        $lock = $this->appLock
            ->createLock([$message->getProfile(), self::class])
            ->lifetime((60 / 600))
            ->waitAllTime();


        /** Проверка изменений в карточке */

        //        /** Карточка товара YaMarket */
        //        $MarketProduct = $this->yandexMarketProductRequest
        //            ->profile($Card['profile'])
        //            ->article($Card['article'])
        //            ->find();
        //
        //        /** Если карточка имеется - проверяем изменения */
        //        if($MarketProduct->valid())
        //        {
        //            /** @var YandexMarketProductDTO $YandexMarketProductDTO */
        //            $YandexMarketProductDTO = $MarketProduct->current();
        //
        //            /** Не обновляем карточку если нет изменений (фото параметров) */
        //            if($YandexMarketProductDTO->equals($request) === true)
        //            {
        //                return;
        //            }
        //        }


        $this->marketProductUpdate
            ->profile($message->getProfile())
            ->update($request);

        $this->logger->info(sprintf('Обновили карточку товара %s', $request['offerId']));

        $lock->release();

    }
}
