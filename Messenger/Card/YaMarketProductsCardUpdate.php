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

namespace BaksDev\Yandex\Market\Products\Messenger\Card;

use BaksDev\Core\Lock\AppLockInterface;
use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Yandex\Market\Products\Api\Products\Card\YaMarketProductUpdateCardRequest;
use BaksDev\Yandex\Market\Products\Mapper\YandexMarketMapper;
use BaksDev\Yandex\Market\Products\Messenger\YaMarketProductsPriceUpdate\YaMarketProductsPriceMessage;
use BaksDev\Yandex\Market\Products\Messenger\YaMarketProductsPriceUpdate\YaMarketProductsPriceUpdate;
use BaksDev\Yandex\Market\Products\Messenger\YaMarketProductsStocksUpdate\YaMarketProductsStocksMessage;
use BaksDev\Yandex\Market\Products\Repository\Card\CurrentYaMarketProductsCard\YaMarketProductsCardInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class YaMarketProductsCardUpdate
{
    public function __construct(
        #[Target('yandexMarketProductsLogger')] private LoggerInterface $logger,
        private YaMarketProductUpdateCardRequest $marketProductUpdate,
        private YaMarketProductsCardInterface $marketProductsCard,
        private YandexMarketMapper $yandexMarketMapper,
        private MessageDispatchInterface $messageDispatch,
        private AppLockInterface $appLock,
    ) {}

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

        /** Лимит: 600 запросов в минуту на расчет и обновление цен, добавляем лок на 0.6 сек */
        $lock = $this->appLock
            ->createLock([$message->getProfile(), YaMarketProductsPriceUpdate::class])
            ->lifetime((60 / 100))
            ->waitAllTime();


        /** Гидрируем карточку на свойства запроса */
        $request = $this->yandexMarketMapper->getData($Card);

        $update = $this->marketProductUpdate
            ->profile($message->getProfile())
            ->update($request);

        if(false === $update)
        {
            /**
             * Ошибка запишется в лог
             * @see YaMarketProductUpdateCardRequest
             */
            return;
        }


        /** Добавляем в очередь обновление цены  */
        $this->messageDispatch->dispatch(
            message: new YaMarketProductsPriceMessage($message),
            transport: (string) $message->getProfile()
        );

        /** Добавляем в очередь обновление остатков  */
        $this->messageDispatch->dispatch(
            message: new YaMarketProductsStocksMessage($message),
            transport: (string) $message->getProfile()
        );

        $this->logger->info(sprintf('Обновили карточку товара %s', $request['offerId']));

        $lock->release();

    }
}
