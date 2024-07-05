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

namespace BaksDev\Yandex\Market\Products\Repository\Card\ProductYaMarketCard;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Products\Product\Entity\Product;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Yandex\Market\Products\Entity\Card\Market\YaMarketProductsCardMarket;
use BaksDev\Yandex\Market\Products\Entity\Card\Modify\YaMarketProductsCardModify;

final class ProductsYaMarketCardRepository implements ProductsYaMarketCardInterface
{
    private DBALQueryBuilder $DBALQueryBuilder;

    private ?ProductUid $product = null;

    private ?UserProfileUid $profile = null;

    public function __construct(
        DBALQueryBuilder $DBALQueryBuilder,
    ) {
        $this->DBALQueryBuilder = $DBALQueryBuilder;
    }

    /**
     * Метод присваивает фильтр по идентификатору продукции
     */
    public function whereProduct(Product|ProductUid|string $product): self
    {
        if(is_string($product))
        {
            $product = new ProductUid($product);
        }

        if($product instanceof Product)
        {
            $product = $product->getId();
        }

        $this->product = $product;

        return $this;
    }

    /**
     * Метод присваивает фильтр по идентификатору профиля пользователя
     */
    public function whereProfile(UserProfileUid|string $profile): self
    {
        if(is_string($profile))
        {
            $profile = new UserProfileUid($profile);
        }

        $this->profile = $profile;

        return $this;
    }

    /**
     * Метод получает все карточки товара
     */
    public function findAll(): ?array
    {
        $dbal = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $dbal
            ->select('card.main')
            ->addSelect('card.event')
            ->addSelect('card.profile')
            ->addSelect('card.sku')
            ->addSelect('card.product')
            ->addSelect('card.offer')
            ->addSelect('card.variation')
            ->addSelect('card.modification')
            ->from(YaMarketProductsCardMarket::class, 'card');


        $dbal->leftJoin(
            'card',
            YaMarketProductsCardModify::class,
            'modify',
            'modify.event = card.event'
        );


        if($this->profile)
        {
            $dbal
                ->where('card.profile = :profile')
                ->setParameter('profile', $this->profile, UserProfileUid::TYPE);
        }

        if($this->product)
        {
            $dbal
                ->where('card.product = :product')
                ->setParameter('product', $this->product, ProductUid::TYPE);
        }


        $dbal->orderBy('modify.mod_date', 'ASC');

        return $dbal
            ->enableCache('yandex-market-products', 86400)
            ->fetchAllAssociative();
    }
}
