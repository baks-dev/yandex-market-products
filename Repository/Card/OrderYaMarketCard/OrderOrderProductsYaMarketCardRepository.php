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

namespace BaksDev\Yandex\Market\Products\Repository\Card\OrderYaMarketCard;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Orders\Order\Entity\Event\OrderEvent;
use BaksDev\Orders\Order\Entity\Order;
use BaksDev\Orders\Order\Entity\Products\OrderProduct;
use BaksDev\Orders\Order\Type\Id\OrderUid;
use BaksDev\Products\Product\Entity\Event\ProductEvent;
use BaksDev\Products\Product\Entity\Info\ProductInfo;
use BaksDev\Products\Product\Entity\Offers\ProductOffer;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\ProductModification;
use BaksDev\Products\Product\Entity\Offers\Variation\ProductVariation;
use BaksDev\Yandex\Market\Products\Entity\Card\Market\YaMarketProductsCardMarket;

final class OrderOrderProductsYaMarketCardRepository implements OrderProductsYaMarketCardInterface
{
    private DBALQueryBuilder $DBALQueryBuilder;

    public function __construct(
        DBALQueryBuilder $DBALQueryBuilder,
    ) {
        $this->DBALQueryBuilder = $DBALQueryBuilder;
    }

    /**
     * Метод получает все карточки, имеющиеся в заказе
     */
    public function findAll(Order|OrderUid|string $order): ?array
    {
        if(is_string($order))
        {
            $order = new OrderUid($order);
        }

        if($order instanceof Order)
        {
            $order = $order->getId();
        }

        $dbal = $this->DBALQueryBuilder->createQueryBuilder(self::class);


        $dbal
            ->from(Order::class, 'ord')
            ->where('ord.id = :ord')
            ->setParameter('ord', $order, OrderUid::TYPE);

        $dbal
            ->leftJoin(
                'ord',
                OrderEvent::class,
                'ord_event',
                'ord_event.id = ord.event'
            );

        $dbal
            ->leftJoin(
                'ord',
                OrderProduct::class,
                'ord_product',
                'ord_product.event = ord.event'
            );


        $dbal
            ->leftJoin(
                'ord_product',
                ProductEvent::class,
                'product_event',
                'product_event.id = ord_product.product'
            );


        $dbal->leftJoin(
            'ord_product',
            ProductOffer::class,
            'product_offer',
            'product_offer.id = ord_product.offer'
        );

        $dbal->leftJoin(
            'ord_product',
            ProductVariation::class,
            'product_variation',
            'product_variation.id = ord_product.variation'
        );

        $dbal->leftJoin(
            'ord_product',
            ProductModification::class,
            'product_modification',
            'product_modification.id = ord_product.modification'
        );

        $dbal
            ->leftJoin(
                'product_event',
                ProductInfo::class,
                'product_info',
                'product_info.product = product_event.main'
            );

        $dbal
            ->select('card.main')
            ->addSelect('card.event')
            ->addSelect('card.profile')
            ->addSelect('card.sku')
            ->addSelect('card.product')
            ->addSelect('card.offer')
            ->addSelect('card.variation')
            ->addSelect('card.modification')
            ->leftJoin(
                'product_event',
                YaMarketProductsCardMarket::class,
                'card',
                '
                    card.product = product_event.main AND
                    card.offer = product_offer.const AND
                    card.variation = product_variation.const AND
                    card.modification = product_modification.const
            '
            );

        return $dbal->fetchAllAssociative();
    }
}
