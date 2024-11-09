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
    private string $profile;

    /**
     * ID продукта
     */
    private string $product;

    /**
     * Постоянный уникальный идентификатор ТП
     */
    private string|null $offerConst;

    /**
     * Постоянный уникальный идентификатор варианта
     */
    private string|null $variationConst;

    /**
     * Постоянный уникальный идентификатор модификации
     */
    private string|null $modificationConst;

    public function __construct(
        UserProfileUid|string $profile,
        Product|ProductUid|string $product,
        ProductOfferConst|string|null $offerConst,
        ProductVariationConst|string|null $variationConst,
        ProductModificationConst|string|null $modificationConst
    )
    {
        $this->profile = (string) $profile;
        $this->product = (string) $product;
        $this->offerConst = (string) $offerConst;
        $this->variationConst = (string) $variationConst;
        $this->modificationConst = (string) $modificationConst;
    }

    /**
     * Profile
     */
    public function getProfile(): UserProfileUid
    {
        return new UserProfileUid($this->profile);
    }

    /**
     * Product
     */
    public function getProduct(): ProductUid
    {
        return new ProductUid($this->product);
    }

    /**
     * OfferConst
     */
    public function getOfferConst(): ?ProductOfferConst
    {
        return $this->offerConst ? new ProductOfferConst($this->offerConst) : null;
    }

    /**
     * VariationConst
     */
    public function getVariationConst(): ?ProductVariationConst
    {
        return $this->variationConst ? new ProductVariationConst($this->variationConst) : null;
    }

    /**
     * ModificationConst
     */
    public function getModificationConst(): ?ProductModificationConst
    {
        return $this->modificationConst ? new ProductModificationConst($this->modificationConst) : null;
    }


}
