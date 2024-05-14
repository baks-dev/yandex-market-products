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

namespace BaksDev\Yandex\Market\Products\Repository\Card\CurrentProductEvent;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Products\Product\Entity\Offers\ProductOffer;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\ProductModification;
use BaksDev\Products\Product\Entity\Offers\Variation\ProductVariation;
use BaksDev\Products\Product\Entity\Product;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Yandex\Market\Products\Entity\Card\Market\YaMarketProductsCardMarket;


final class CurrentProductEventByArticle implements CurrentProductEventByArticleInterface
{
    private DBALQueryBuilder $DBALQueryBuilder;

    private string|UserProfileUid|null $profile = null;

    public function __construct(
        DBALQueryBuilder $DBALQueryBuilder,
    )
    {
        $this->DBALQueryBuilder = $DBALQueryBuilder;
    }

    public function profile(UserProfileUid|string $profile): self
    {
        if(is_string($profile))
        {
            $profile = new UserProfileUid($profile);
        }

        $this->profile = $profile;

        return $this;
    }

    /**
     * Метод возвращает активное событие продукта по артикулу карточки YandexMarket
     */
    public function find(string $article): array|bool
    {
        $dbal = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $dbal
            ->from(YaMarketProductsCardMarket::class, 'card_market')
            ->where('card_market.sku = :article')
            ->setParameter('article', $article);

        if($this->profile)
        {
            $dbal
                ->andWhere('card_market.profile = :profile')
                ->setParameter('profile', $this->profile, UserProfileUid::TYPE);
        }

        $dbal
            ->addSelect('product.event AS product_event_uid')
            ->join(
                'card_market',
                Product::class,
                'product',
                'product.id = card_market.product'
            );

        $dbal
            ->addSelect('product_offer.id AS product_offer_uid')
            ->join(
                'product',
                ProductOffer::class,
                'product_offer',
                '
                    product_offer.event = product.event AND 
                    product_offer.const = card_market.offer
            ');

        $dbal
            ->addSelect('product_variation.id AS product_variation_uid')
            ->join(
                'product_offer',
                ProductVariation::class,
                'product_variation',
                '
                product_variation.offer = product_offer.id AND 
                product_variation.const = card_market.variation
            ');

        $dbal
            ->addSelect('product_modification.id AS product_modification_uid')
            ->join(
                'product_variation',
                ProductModification::class,
                'product_modification',
                '
                product_modification.variation = product_variation.id AND 
                product_modification.const = card_market.modification
            ');


        $dbal->setMaxResults(1);

        return $dbal->fetchAssociative();
    }
}