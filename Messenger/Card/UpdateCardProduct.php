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
use BaksDev\Products\Product\Messenger\ProductMessage;
use BaksDev\Yandex\Market\Products\Repository\Card\ProductYaMarketCard\ProductsYaMarketCardInterface;
use BaksDev\Yandex\Market\Products\Type\Card\Event\YaMarketProductsCardEventUid;
use BaksDev\Yandex\Market\Products\Type\Card\Id\YaMarketProductsCardUid;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class UpdateCardProduct
{
    private MessageDispatchInterface $messageDispatch;
    private ProductsYaMarketCardInterface $productsYaMarketCard;

    public function __construct(
        ProductsYaMarketCardInterface $productsYaMarketCard,
        MessageDispatchInterface $messageDispatch,
    )
    {

        $this->messageDispatch = $messageDispatch;
        $this->productsYaMarketCard = $productsYaMarketCard;
    }
    
    /**
     * Обновляем информацию YandexMarket при изменении системной карточки
     */
    public function __invoke(ProductMessage $message): void
    {
        /** Получаем идентификаторы карточки YandexMarket товаров */
        $cards = $this->productsYaMarketCard->findAll($message->getId());

        foreach($cards as $card)
        {
            $YaMarketProductsCardUid = new YaMarketProductsCardUid($card['main']);
            $YaMarketProductsCardEventUid = new YaMarketProductsCardEventUid($card['event']);

            /* Отправляем сообщение в шину */
            $this->messageDispatch->dispatch(
                message: new YaMarketProductsCardMessage($YaMarketProductsCardUid, $YaMarketProductsCardEventUid),
                transport: 'yandex-market-products'
            );
        }
    }
}