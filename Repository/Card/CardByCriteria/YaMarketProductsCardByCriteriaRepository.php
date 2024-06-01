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

namespace BaksDev\Yandex\Market\Products\Repository\Card\CardByCriteria;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Yandex\Market\Products\Entity\Card\Market\YaMarketProductsCardMarket;
use BaksDev\Yandex\Market\Products\Entity\Card\YaMarketProductsCard;
use BaksDev\Yandex\Market\Products\Messenger\Card\YaMarketProductsCardMessage;
use BaksDev\Yandex\Market\Products\Messenger\Card\YaMarketProductsStocksUpdate\YaMarketProductsStocksMessage;
use Generator;
use InvalidArgumentException;


final class YaMarketProductsCardByCriteriaRepository implements YaMarketProductsCardByCriteriaInterface
{
    private DBALQueryBuilder $DBALQueryBuilder;

    public function __construct(
        DBALQueryBuilder $DBALQueryBuilder,
    )
    {
        $this->DBALQueryBuilder = $DBALQueryBuilder;
    }

    /**
     * ID продукта
     */
    private ?ProductUid $product = null;

    /**
     * Постоянный уникальный идентификатор ТП
     */
    private ?ProductOfferConst $offer = null;

    /**
     * Постоянный уникальный идентификатор варианта
     */
    private ?ProductVariationConst $variation = null;

    /**
     * Постоянный уникальный идентификатор модификации
     */
    private ?ProductModificationConst $modification = null;


    public function product(ProductUid|string $product): self
    {
        if(is_string($product))
        {
            $product = new ProductUid($product);
        }

        $this->product = $product;
        return $this;
    }

    public function offer(ProductOfferConst|string|null $offer): self
    {
        if(!$offer)
        {
            return $this;
        }

        if(is_string($offer))
        {
            $offer = new ProductOfferConst($offer);
        }

        $this->offer = $offer;
        return $this;
    }

    public function variation(ProductVariationConst|string|null $variation): self
    {
        if(!$variation)
        {
            return $this;
        }

        if(is_string($variation))
        {
            $variation = new ProductVariationConst($variation);
        }

        $this->variation = $variation;
        return $this;
    }

    public function modification(ProductModificationConst|string|null $modification): self
    {
        if(!$modification)
        {
            return $this;
        }

        if(is_string($modification))
        {
            $modification = new ProductModificationConst($modification);
        }

        $this->modification = $modification;
        return $this;
    }


    public function findByProfile(UserProfileUid|string $profile): ?YaMarketProductsStocksMessage
    {
        if(is_string($profile))
        {
            $profile = new UserProfileUid($profile);
        }

        if(empty($this->product))
        {
            throw new InvalidArgumentException('Invalid Argument product');
        }

        $dbal = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $dbal
            ->from(YaMarketProductsCardMarket::class, 'market')
            ->where('market.profile = :profile')
            ->setParameter('profile', $profile);


        $dbal
            ->andWhere('market.product = :product')
            ->setParameter('product', $this->product);


        if($this->offer)
        {
            $dbal
                ->andWhere('market.offer = :offer')
                ->setParameter('offer', $this->offer);
        }
        else
        {
            $dbal->andWhere('market.offer IS NULL');
        }


        if($this->variation)
        {
            $dbal
                ->andWhere('market.variation = :variation')
                ->setParameter('variation', $this->variation);
        }
        else
        {
            $dbal->andWhere('market.variation IS NULL');
        }


        if($this->modification)
        {
            $dbal
                ->andWhere('market.modification = :modification')
                ->setParameter('modification', $this->modification);
        }
        else
        {
            $dbal->andWhere('market.modification IS NULL');
        }


        $dbal
            ->join(
                'market',
                YaMarketProductsCard::class,
                'main',
                'main.event = market.event'
            );


        /** Параметры конструктора объекта гидрации */

        $dbal->select('main.id');
        $dbal->addSelect('main.event');

        return $dbal->fetchHydrate(YaMarketProductsStocksMessage::class);
    }
}