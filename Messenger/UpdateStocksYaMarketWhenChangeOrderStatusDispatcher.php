<?php
/*
 *  Copyright 2026.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Yandex\Market\Products\Messenger;

use BaksDev\Core\Deduplicator\DeduplicatorInterface;
use BaksDev\Core\Messenger\MessageDelay;
use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Orders\Order\Entity\Event\OrderEvent;
use BaksDev\Orders\Order\Messenger\OrderMessage;
use BaksDev\Orders\Order\Repository\CurrentOrderEvent\CurrentOrderEventInterface;
use BaksDev\Orders\Order\UseCase\Admin\Edit\EditOrderDTO;
use BaksDev\Orders\Order\UseCase\Admin\Edit\Products\OrderProductDTO;
use BaksDev\Products\Product\Repository\CurrentProductIdentifier\CurrentProductIdentifierByEventInterface;
use BaksDev\Products\Product\Repository\CurrentProductIdentifier\CurrentProductIdentifierResult;
use BaksDev\Yandex\Market\Products\Messenger\Card\YaMarketProductsCardMessage;
use BaksDev\Yandex\Market\Products\Messenger\YaMarketProductsStocksUpdate\YaMarketProductsStocksMessage;
use BaksDev\Yandex\Market\Repository\AllProfileToken\AllProfileYaMarketTokenInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Обновляем остатки YandexMarket при изменении статусов заказов
 */
#[AsMessageHandler(priority: 90)]
final readonly class UpdateStocksYaMarketWhenChangeOrderStatusDispatcher
{
    public function __construct(
        private CurrentOrderEventInterface $CurrentOrderEvent,
        private CurrentProductIdentifierByEventInterface $currentProductIdentifier,
        private AllProfileYaMarketTokenInterface $allProfileYaMarketToken,
        private MessageDispatchInterface $messageDispatch,
        private DeduplicatorInterface $deduplicator,
        #[Autowire(env: 'PROJECT_PROFILE')] private ?string $PROJECT_PROFILE = null,
    ) {}


    public function __invoke(OrderMessage $message): void
    {
        /**  Получаем активные токены профилей пользователя */

        $profiles = $this->allProfileYaMarketToken
            ->onlyActiveToken()
            ->findAll();

        if(false === $profiles || false === $profiles->valid())
        {
            return;
        }


        /** Получаем событие заказа */
        $OrderEvent = $this->CurrentOrderEvent
            ->forOrder($message->getId())
            ->find();

        if(false === ($OrderEvent instanceof OrderEvent))
        {
            return;
        }

        /** Дедубликатор изменения статусов (обновляем только один раз в сутки на статус) */

        $Deduplicator = $this->deduplicator
            ->namespace('ozon-products')
            ->expiresAfter('1 day')
            ->deduplication([
                (string) $message->getId(),
                $OrderEvent->getStatus()->getOrderStatusValue(),
                self::class,
            ]);

        if($Deduplicator->isExecuted())
        {
            return;
        }

        $Deduplicator->save();

        /**
         * Обновляем остатки
         */

        $EditOrderDTO = new EditOrderDTO();
        $OrderEvent->getDto($EditOrderDTO);

        foreach($profiles as $UserProfileUid)
        {
            /** Если указан профиль проекта - пропускаем остальные профили */
            if(false === empty($this->PROJECT_PROFILE) && false === $UserProfileUid->equals($this->PROJECT_PROFILE))
            {
                continue;
            }

            /** @var OrderProductDTO $product */
            foreach($EditOrderDTO->getProduct() as $product)
            {
                /** Получаем идентификаторы обновляемой продукции для получения констант  */
                $CurrentProductIdentifier = $this->currentProductIdentifier
                    ->forEvent($product->getProduct())
                    ->forOffer($product->getOffer())
                    ->forVariation($product->getVariation())
                    ->forModification($product->getModification())
                    ->find();

                if(false === ($CurrentProductIdentifier instanceof CurrentProductIdentifierResult))
                {
                    continue;
                }

                $YaMarketProductsCardMessage = new YaMarketProductsCardMessage(
                    $UserProfileUid,
                    $CurrentProductIdentifier->getProduct(),
                    $CurrentProductIdentifier->getOfferConst(),
                    $CurrentProductIdentifier->getVariationConst(),
                    $CurrentProductIdentifier->getModificationConst(),
                );

                /** Добавляем в очередь обновление остатков через транспорт профиля */

                $this->messageDispatch->dispatch(
                    message: new YaMarketProductsStocksMessage($YaMarketProductsCardMessage),
                    stamps: [new MessageDelay('5 seconds')],
                    transport: (string) $UserProfileUid,
                );


                /** Дополнительно пробуем обновить (на случай если остатки еще не успели пересчитаться) */

                $this->messageDispatch->dispatch(
                    message: new YaMarketProductsStocksMessage($YaMarketProductsCardMessage),
                    stamps: [new MessageDelay('15 seconds')],
                    transport: (string) $UserProfileUid,
                );

            }
        }
    }
}
