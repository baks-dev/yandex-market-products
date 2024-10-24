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

namespace BaksDev\Yandex\Market\Products\Messenger\Card;

use BaksDev\Products\Product\Entity\Product;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;

final class YaMarketProductsCardMessage
{
    /**
     * Идентификатор профиля пользователя
     */
    private UserProfileUid $profile;

    /**
     * ID продукта
     */
    private ProductUid $product;

    /**
     * Постоянный уникальный идентификатор ТП
     */
    private ProductOfferConst|null $offerConst;

    /**
     * Постоянный уникальный идентификатор варианта
     */
    private ProductVariationConst|null $variationConst;

    /**
     * Постоянный уникальный идентификатор модификации
     */
    private ProductModificationConst|null $modificationConst;

    public function __construct(
        UserProfileUid|string $profile,
        Product|ProductUid|string $product,
        ProductOfferConst|string|null $offerConst,
        ProductVariationConst|string|null $variationConst,
        ProductModificationConst|string|null $modificationConst
    )
    {

        /** UserProfileUid */

        if(is_string($profile))
        {
            $profile = new UserProfileUid($profile);
        }

        $this->profile = $profile;


        /** ProductUid */

        if(is_string($product))
        {
            $product = new ProductUid($product);
        }

        if($product instanceof Product)
        {
            $product = $product->getId();
        }

        $this->product = $product;


        /** ProductOfferConst */

        if(empty($offerConst))
        {
            $offerConst = null;
        }

        if(is_string($offerConst))
        {
            $offerConst = new ProductOfferConst($offerConst);
        }

        $this->offerConst = $offerConst;

        /** ProductVariationConst */

        if(empty($variationConst))
        {
            $variationConst = null;
        }

        if(is_string($variationConst))
        {
            $variationConst = new ProductVariationConst($variationConst);
        }

        $this->variationConst = $variationConst;


        /** ProductModificationConst */

        if(empty($modificationConst))
        {
            $modificationConst = null;
        }

        if(is_string($modificationConst))
        {
            $modificationConst = new ProductModificationConst($modificationConst);
        }

        $this->modificationConst = $modificationConst;

    }

    /**
     * Profile
     */
    public function getProfile(): UserProfileUid
    {
        return $this->profile;
    }

    /**
     * Product
     */
    public function getProduct(): ProductUid|false
    {
        return $this->product;
    }

    /**
     * OfferConst
     */
    public function getOfferConst(): ?ProductOfferConst
    {
        return $this->offerConst;
    }

    /**
     * VariationConst
     */
    public function getVariationConst(): ?ProductVariationConst
    {
        return $this->variationConst;
    }

    /**
     * ModificationConst
     */
    public function getModificationConst(): ?ProductModificationConst
    {
        return $this->modificationConst;
    }


}
