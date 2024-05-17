<?php
/*
 *  Copyright 2023.  Baks.dev <admin@baks.dev>
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

use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Orders\Order\Messenger\OrderMessage;
use BaksDev\Yandex\Market\Products\Messenger\Card\YaMarketProductsStocksUpdate\YaMarketProductsStocksMessage;
use BaksDev\Yandex\Market\Products\Repository\Card\OrderYaMarketCard\OrderProductsYaMarketCardInterface;
use BaksDev\Yandex\Market\Products\Type\Card\Event\YaMarketProductsCardEventUid;
use BaksDev\Yandex\Market\Products\Type\Card\Id\YaMarketProductsCardUid;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class UpdateCardOrder
{
    private MessageDispatchInterface $messageDispatch;
    private OrderProductsYaMarketCardInterface $orderProductsYaMarketCard;

    public function __construct(
        OrderProductsYaMarketCardInterface $orderProductsYaMarketCard,
        MessageDispatchInterface $messageDispatch,
    )
    {
        $this->orderProductsYaMarketCard = $orderProductsYaMarketCard;
        $this->messageDispatch = $messageDispatch;
    }

    /**
     * Обновляем информацию YandexMarket при изменении статусов заказов
     */
    public function __invoke(OrderMessage $message): void
    {
        /** Получаем идентификаторы карточки YandexMarket товаров в заказе */
        $cards = $this->orderProductsYaMarketCard->findAll($message->getId());

        foreach($cards as $card)
        {
            $YaMarketProductsCardUid = new YaMarketProductsCardUid($card['main']);
            $YaMarketProductsCardEventUid = new YaMarketProductsCardEventUid($card['event']);
            $YaMarketProductsCardMessage = new YaMarketProductsCardMessage($YaMarketProductsCardUid, $YaMarketProductsCardEventUid);

            /* Обновляем только остатки */
            $this->messageDispatch->dispatch(
                message: new YaMarketProductsStocksMessage($YaMarketProductsCardMessage),
                transport: $card['profile']
            );
        }
    }
}