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

use BaksDev\Core\Cache\AppCacheInterface;
use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Products\Product\Messenger\ProductMessage;
use BaksDev\Yandex\Market\Products\Repository\Card\ProductYaMarketCard\ProductsYaMarketCardInterface;
use BaksDev\Yandex\Market\Products\Type\Card\Event\YaMarketProductsCardEventUid;
use BaksDev\Yandex\Market\Products\Type\Card\Id\YaMarketProductsCardUid;
use DateInterval;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class UpdateCardProduct
{
    private MessageDispatchInterface $messageDispatch;
    private ProductsYaMarketCardInterface $productsYaMarketCard;
    private AppCacheInterface $cache;

    public function __construct(
        AppCacheInterface $cache,
        ProductsYaMarketCardInterface $productsYaMarketCard,
        MessageDispatchInterface $messageDispatch,
    )
    {
        $this->messageDispatch = $messageDispatch;
        $this->productsYaMarketCard = $productsYaMarketCard;
        $this->cache = $cache;
    }
    
    /**
     * Обновляем информацию YandexMarket при изменении системной карточки
     */
    public function __invoke(ProductMessage $message): void
    {
        $cache = $this->cache->init('yandex-market-products');

        /** Получаем идентификаторы карточки YandexMarket товаров */
        $cards = $this->productsYaMarketCard->findAll($message->getId());

        foreach($cards as $card)
        {
            $item = $cache->getItem($card['main']);

            if(false === $item->isHit())
            {
                /* Сохраняем идентификатор, предотвращая повторные сообщения */
                $item->set(true);
                $item->expiresAfter(DateInterval::createFromDateString('5 minutes'));
                $cache->save($item);

                /* Отправляем сообщение в шину */
                $YaMarketProductsCardUid = new YaMarketProductsCardUid($card['main']);
                $YaMarketProductsCardEventUid = new YaMarketProductsCardEventUid($card['event']);

                $this->messageDispatch->dispatch(
                    message: new YaMarketProductsCardMessage($YaMarketProductsCardUid, $YaMarketProductsCardEventUid),
                    transport: $card['profile']
                );
            }
        }
    }
}