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

namespace BaksDev\Yandex\Market\Products\Messenger\Orders;

use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Orders\Order\Messenger\OrderMessage;
use BaksDev\Yandex\Market\Products\Messenger\Card\YaMarketProductsCardMessage;
use BaksDev\Yandex\Market\Products\Messenger\YaMarketProductsStocksUpdate\YaMarketProductsStocksMessage;
use BaksDev\Yandex\Market\Products\Repository\Card\OrderYaMarketCard\OrderProductsYaMarketCardInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(priority: 100)]
final class UpdateYaMarketCardByChangeOrderStatus
{
    private MessageDispatchInterface $messageDispatch;
    private OrderProductsYaMarketCardInterface $orderProductsYaMarketCard;

    public function __construct(
        OrderProductsYaMarketCardInterface $orderProductsYaMarketCard,
        MessageDispatchInterface $messageDispatch,
    ) {
        $this->orderProductsYaMarketCard = $orderProductsYaMarketCard;
        $this->messageDispatch = $messageDispatch;
    }

    /**
     * Обновляем остатки YandexMarket при изменении статусов заказов
     */
    public function __invoke(OrderMessage $message): void
    {
        /** Получаем идентификаторы карточек YandexMarket товаров в заказе */
        $cards = $this->orderProductsYaMarketCard->findAll($message->getId());

        foreach($cards as $card)
        {
            $YaMarketProductsCardMessage = new YaMarketProductsCardMessage($card['main'], $card['event']);

            /** Добавляем в очередь обновление остатков через транспорт профиля */
            $this->messageDispatch->dispatch(
                message: new YaMarketProductsStocksMessage($YaMarketProductsCardMessage),
                transport: $card['profile']
            );
        }
    }
}
