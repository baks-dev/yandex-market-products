<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Yandex\Market\Products\Messenger\YaMarketProductsPriceUpdate;

use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Yandex\Market\Products\Messenger\Card\YaMarketProductsCardMessage;

final class YaMarketProductsPriceMessage
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
    private ProductOfferConst|false $offerConst;

    /**
     * Постоянный уникальный идентификатор варианта
     */
    private ProductVariationConst|false $variationConst;

    /**
     * Постоянный уникальный идентификатор модификации
     */
    private ProductModificationConst|false $modificationConst;

    public function __construct(YaMarketProductsCardMessage $message)
    {
        $this->profile = $message->getProfile();
        $this->product = $message->getProduct();

        $this->offerConst = $message->getOfferConst();
        $this->variationConst = $message->getVariationConst();
        $this->modificationConst = $message->getModificationConst();
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
    public function getProduct(): ProductUid
    {
        return $this->product;
    }

    /**
     * OfferConst
     */
    public function getOfferConst(): ProductOfferConst|false
    {
        return $this->offerConst ?: false;
    }

    /**
     * VariationConst
     */
    public function getVariationConst(): ProductVariationConst|false
    {
        return $this->variationConst ?: false;
    }

    /**
     * ModificationConst
     */
    public function getModificationConst(): ProductModificationConst|false
    {
        return $this->modificationConst ?: false;
    }
}
