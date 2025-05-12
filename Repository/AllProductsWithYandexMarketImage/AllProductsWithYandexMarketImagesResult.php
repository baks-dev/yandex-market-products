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

namespace BaksDev\Yandex\Market\Products\Repository\AllProductsWithYandexMarketImage;

use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Invariable\ProductInvariableUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationUid;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
final readonly class AllProductsWithYandexMarketImagesResult
{
    public function __construct(
        private string $id,
        private string $event,
        private string $invariable,
        private bool $category_active,
        private string $category_name,
        private string $product_name,
        private ?string $product_offer_id = null,
        private ?string $product_offer_value = null,
        private ?string $product_offer_const = null,
        private ?string $product_offer_postfix = null,
        private ?string $product_offer_reference = null,
        private ?string $product_variation_id = null,
        private ?string $product_variation_value = null,
        private ?string $product_variation_const = null,
        private ?string $product_variation_postfix = null,
        private ?string $product_variation_reference = null,
        private ?string $product_modification_id = null,
        private ?string $product_modification_value = null,
        private ?string $product_modification_const = null,
        private ?string $product_modification_postfix = null,
        private ?string $product_modification_reference = null,
        private ?string $product_article = null,
        private ?string $product_image = null,
        private ?string $product_image_ext = null,
        private ?bool $product_image_cdn = null,
        private ?string $ya_market_product_id = null,
        private ?string $ya_market_product_image = null,
        private ?string $ya_market_product_image_ext = null,
        private ?bool $ya_market_product_image_cdn = null,
    ) {}

    public function getId(): ProductUid
    {
        return new ProductUid($this->id);
    }

    public function getEvent(): ProductEventUid
    {
        return new ProductEventUid($this->event);
    }

    public function getInvariable(): ProductInvariableUid
    {
        return new ProductInvariableUid($this->invariable);
    }

    public function getProductName(): string
    {
        return $this->product_name;
    }

    public function getProductOfferId(): ?ProductOfferUid
    {
        return new ProductOfferUid($this->product_offer_id);
    }

    public function getProductOfferValue(): ?string
    {
        return $this->product_offer_value;
    }

    public function getProductOfferConst(): ?ProductOfferConst
    {
        return new ProductOfferConst($this->product_offer_const);
    }

    public function getProductOfferPostfix(): ?string
    {
        return $this->product_offer_postfix;
    }

    public function getProductOfferReference(): ?string
    {
        return $this->product_offer_reference;
    }

    public function getProductVariationId(): ?ProductVariationUid
    {
        return new ProductVariationUid($this->product_variation_id);
    }

    public function getProductVariationValue(): ?string
    {
        return $this->product_variation_value;
    }

    public function getProductVariationConst(): ?ProductVariationConst
    {
        return new ProductVariationConst($this->product_variation_const);
    }

    public function getProductVariationPostfix(): ?string
    {
        return $this->product_variation_postfix;
    }

    public function getProductVariationReference(): ?string
    {
        return $this->product_variation_reference;
    }

    public function getProductModificationId(): ?ProductModificationUid
    {
        return new ProductModificationUid($this->product_modification_id);
    }

    public function getProductModificationValue(): ?string
    {
        return $this->product_modification_value;
    }

    public function getProductModificationConst(): ?ProductModificationConst
    {
        return new ProductModificationConst($this->product_modification_const);
    }

    public function getProductModificationPostfix(): ?string
    {
        return $this->product_modification_postfix;
    }

    public function getProductModificationReference(): ?string
    {
        return $this->product_modification_reference;
    }

    public function getProductArticle(): ?string
    {
        return $this->product_article;
    }

    public function getProductImage(): ?string
    {
        return $this->product_image;
    }

    public function getProductImageExt(): ?string
    {
        return $this->product_image_ext;
    }

    public function getProductImageCdn(): ?bool
    {
        return $this->product_image_cdn;
    }

    public function getYaMarketProductId(): ?string
    {
        return $this->ya_market_product_id;
    }

    public function getYandexMarketProductImage(): ?string
    {
        return $this->ya_market_product_image;
    }

    public function getYandexMarketProductImageExt(): ?string
    {
        return $this->ya_market_product_image_ext;
    }

    public function getYandexMarketProductImageCdn(): ?bool
    {
        return $this->ya_market_product_image_cdn;
    }

    public function getCategoryActive(): bool
    {
        return $this->category_active;
    }

    public function getCategoryName(): string
    {
        return $this->category_name;
    }
}
