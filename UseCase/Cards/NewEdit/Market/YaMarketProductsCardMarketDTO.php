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

namespace BaksDev\Yandex\Market\Products\UseCase\Cards\NewEdit\Market;

use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Yandex\Market\Products\Entity\Card\Market\YaMarketProductsCardMarketInterface;
use Symfony\Component\Validator\Constraints as Assert;

/** @see YaMarketProductsCardMarket */
final class YaMarketProductsCardMarketDTO implements YaMarketProductsCardMarketInterface
{
    /** Профиль пользователя, которому принадлежит карточка */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    private UserProfileUid $profile;

    /**
     * Артикул
     */
    #[Assert\NotBlank]
    private string $sku;

    /**
     * ID продукта
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    private ProductUid $product;

    /**
     * Постоянный уникальный идентификатор ТП
     */
    #[Assert\Uuid]
    private ?ProductOfferConst $offer;

    /**
     * Постоянный уникальный идентификатор варианта
     */
    #[Assert\Uuid]
    private ?ProductVariationConst $variation;

    /**
     * Постоянный уникальный идентификатор модификации
     */
    #[Assert\Uuid]
    private ?ProductModificationConst $modification;


    public function newInstance(
        string $sku,
        ProductUid|string $product,
        ProductOfferConst|string|null $offer = null,
        ProductVariationConst|string|null $variation = null,
        ProductModificationConst|string|null $modification = null
    ): self
    {

        $this->sku = $sku;
        $this->product = is_string($product) ? new ProductUid($product) : $product;
        $this->offer = is_string($offer) ? new ProductOfferConst($offer) : $offer;
        $this->variation = is_string($variation) ? new ProductVariationConst($variation) : $variation;
        $this->modification = is_string($modification) ? new ProductModificationConst($modification) : $modification;

        return $this;

    }


    /**
     * Sku
     */
    public function getSku(): string
    {
        return $this->sku;
    }

    public function setSku(string $sku): self
    {
        $this->sku = $sku;
        return $this;
    }

    /**
     * Product
     */
    public function getProduct(): ProductUid
    {
        return $this->product;
    }

    public function setProduct(ProductUid $product): self
    {
        $this->product = $product;
        return $this;
    }

    /**
     * Offer
     */
    public function getOffer(): ?ProductOfferConst
    {
        return $this->offer;
    }

    public function setOffer(?ProductOfferConst $offer): self
    {
        $this->offer = $offer;
        return $this;
    }

    /**
     * Variation
     */
    public function getVariation(): ?ProductVariationConst
    {
        return $this->variation;
    }

    public function setVariation(?ProductVariationConst $variation): self
    {
        $this->variation = $variation;
        return $this;
    }

    /**
     * Modification
     */
    public function getModification(): ?ProductModificationConst
    {
        return $this->modification;
    }

    public function setModification(?ProductModificationConst $modification): self
    {
        $this->modification = $modification;
        return $this;
    }

    /**
     * Profile
     */
    public function getProfile(): UserProfileUid
    {
        return $this->profile;
    }

    public function setProfile(UserProfileUid $profile): self
    {
        $this->profile = $profile;
        return $this;
    }

}
