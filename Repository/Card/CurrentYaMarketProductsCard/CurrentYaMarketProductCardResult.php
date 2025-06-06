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

namespace BaksDev\Yandex\Market\Products\Repository\Card\CurrentYaMarketProductsCard;

use BaksDev\Products\Product\Entity\Property\ProductProperty;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
use BaksDev\Reference\Currency\Type\Currency;
use BaksDev\Reference\Measurement\Type\Measurements\MeasurementStunt;
use BaksDev\Reference\Money\Type\Money;
use Symfony\Component\Validator\Constraints as Assert;

/** @see CurrentYaMarketProductCardResult */
final class CurrentYaMarketProductCardResult
{

    private array|false|null $properties = null;

    private array|false|null $params = null;

    private array|false|null $images;

    public function __construct(
        private readonly string $product_uid, // " => "01878a9e-26ff-7f71-bb70-6cb19c044cd6"
        private readonly ?string $product_card, // " => "TR257"

        private readonly ?string $offer_const, // " => "01878a9e-25db-76e0-9ed8-2b4155abda46"
        private readonly ?string $product_offer_value, // " => "15"
        private readonly ?string $product_offer_postfix, // " => null

        private readonly ?string $variation_const, // " => "01878a9e-25df-7cc3-9fca-8e9e3e61d099"
        private readonly ?string $product_variation_value, // " => "235"
        private readonly ?string $product_variation_postfix, // " => null

        private readonly ?string $modification_const, // " => "01878a9e-25e1-7a55-92e1-11d4210d077c"
        private readonly ?string $product_modification_value, // " => "70"
        private readonly ?string $product_modification_postfix, // " => "107H"

        private readonly string $product_name, // " => "Triangle TR257"
        private readonly ?string $product_preview,
        // " => "<p><strong>Летние шины Triangle TR257</strong> - от китайского производителя Triangle Group предназначены для автомобилей класса SUV, таких как кроссоверы и пикапы, в некоторой мере внедорожники, в том числе представительского класса. Относятся к сегменту H/T (Highway Terrain). Данная модель покрышек отлично подойдет тем, кто большую часть времени ездит по городу или трассам. Она может эксплуатироваться не только на шоссе и трассах, но и на гравийных и грунтовых дорогах. Протектор не является «грязевым», поэтому езда по бездорожью не рекомендуется. Это резина с исключительно хорошими ходовыми качествами, которая позволяет уверенно держаться на дороге на скорости, при этом ее мягкость обеспечивает комфорт.</p><p>Шины представлены типоразмерами от 235/70 R15 до 245/55 R19, индексами нагрузки от 95 (до 690 кг) до 112 (до 1120 кг) и индексами скорости T (до 190 км/ч), H (до 210 км/ч), V (до 240 км/ч)</p>"

        private readonly string $category_name, // " => "Triangle"

        private readonly ?string $length, // " => null
        private readonly ?string $width, // " => null
        private readonly ?string $height, // " => null
        private readonly ?string $weight, // " => null

        private readonly ?int $market_category, // " => null
        private readonly ?string $product_properties, // " => "[{"type": null, "value": null}]"
        private readonly ?string $product_params, // " => "[{"name": null, "value": null}]"

        private readonly ?string $product_images, // " => "[{
        //"product_img": "/upload/product_photo/52a58f1569ad1d47162aee8ecca9578d",
        // "product_img_cdn": true,
        // "product_img_ext": "webp",
        // "product_img_root": true|false}]"

        private readonly ?int $product_price, // " => 670000
        private readonly ?int $product_old_price, // " => 0

        private readonly ?string $product_currency, // " => "rub"
        private readonly ?int $product_quantity, //  => null

        private readonly ?string $article, // " => "TR257-15-235-70-107H"
        private readonly ?string $barcode, // " => "2744610541511"

        private ?string $project_discount = null, // " => 0

    ) {}

    /** Метод проверяет свойства, без которых нельзя получить карточку */
    public function isCredentials(): bool
    {
        if(empty($this->product_price))
        {
            return false;
        }

        if(empty($this->length))
        {
            return false;
        }

        if(empty($this->width))
        {
            return false;
        }

        if(empty($this->height))
        {
            return false;
        }

        if(empty($this->weight))
        {
            return false;
        }

        return true;
    }


    public function getProductId(): ProductUid
    {
        return new ProductUid($this->product_uid);
    }

    /**
     * Объединяющий в карточку параметр
     *
     * @example "TR257"
     */
    public function getGroupCard(): string|false
    {
        return $this->product_card ?: false;
    }


    public function getOfferConst(): ProductOfferConst|false
    {
        return $this->offer_const ? new ProductOfferConst($this->offer_const) : false;
    }

    public function getProductOfferValue(): ?string
    {
        return $this->product_offer_value;
    }

    public function getProductOfferPostfix(): ?string
    {
        return $this->product_offer_postfix;
    }

    /**
     * Variation
     */

    public function getVariationConst(): ProductVariationConst|false
    {
        return $this->variation_const ? new ProductVariationConst($this->variation_const) : false;
    }

    public function getProductVariationValue(): ?string
    {
        return $this->product_variation_value;
    }

    public function getProductVariationPostfix(): ?string
    {
        return $this->product_variation_postfix;
    }

    /**
     * Modification
     */

    public function getModificationConst(): ProductModificationConst|false
    {
        return $this->modification_const ? new ProductModificationConst($this->modification_const) : false;
    }

    public function getProductModificationValue(): ?string
    {
        return $this->product_modification_value;
    }

    public function getProductModificationPostfix(): ?string
    {
        return $this->product_modification_postfix;
    }


    public function getProductName(): string
    {
        return $this->product_name;
    }

    public function getProductPreview(): ?string
    {
        return $this->product_preview;
    }


    public function getCategoryName(): string
    {
        return $this->category_name;
    }

    /**
     * Длина упаковки в см
     */
    public function getLength(): float|false
    {
        return $this->length ? ($this->length * 0.1) : false;
    }

    /**
     * Ширина упаковки в см
     */
    public function getWidth(): float|false
    {
        return $this->width ? ($this->width * 0.1) : false;
    }

    /**
     * Высота упаковки в см
     */
    public function getHeight(): float|false
    {
        return $this->height ? ($this->height * 0.1) : false;
    }

    /**
     * Вес товара в кг с учетом упаковки (брутто)
     */
    public function getWeight(): float|false
    {
        return $this->weight ? ($this->weight * 0.01) : false;
    }

    /**
     * MarketCategory
     */
    public function getMarketCategory(): int
    {
        return $this->market_category;
    }

    /**
     * ProductProperties
     */
    public function getProductProperties(): array|false
    {
        /**
         * Возвращаем если свойство уже присвоено
         */
        if(false === is_null($this->properties))
        {
            return $this->properties;
        }

        if(empty($this->product_properties))
        {
            $this->properties = false;
            return false;
        }

        if(false === json_validate($this->product_properties))
        {
            $this->properties = false;
            return false;
        }

        $data = json_decode($this->product_properties, false, 512, JSON_THROW_ON_ERROR);
        $data = array_filter($data, static fn($item) => empty($item->type) === false);

        return $this->properties = empty($data) ? false : $data;
    }

    /**
     * ProductParams
     */
    public function getProductParams(): array|false
    {
        if(false === is_null($this->params))
        {
            return $this->params;
        }

        if(empty($this->product_params))
        {
            $this->params = false;
            return false;
        }

        if(false === json_validate($this->product_params))
        {
            $this->params = false;
            return false;
        }

        $data = json_decode($this->product_params, false, 512, JSON_THROW_ON_ERROR);
        $data = array_filter($data, static fn($item) => empty($item->name) === false);

        return $this->params = empty($data) ? false : $data;
    }

    /**
     * ProductImages
     */
    public function getProductImages(): array|false
    {
        if(false === is_null($this->images))
        {
            return $this->images;
        }

        if(empty($this->product_images))
        {
            $this->images = false;
            return false;
        }

        if(false === json_validate($this->product_images))
        {
            $this->images = false;
            return false;
        }

        $images = json_decode($this->product_images, false, 512, JSON_THROW_ON_ERROR);
        $images = array_filter($images, static fn($item) => empty($item->product_img) === false);

        if(empty($images))
        {
            $this->images = false;
            return false;
        }

        /* Сортируем коллекцию изображений по root */
        usort($images, static function($img) {
            return ($img->product_img_root === false) ? 1 : -1;
        });

        return $this->images = $images;
    }

    public function getProductPrice(): Money|false
    {
        if(empty($this->product_price))
        {
            return false;
        }

        $price = new Money($this->product_price, true);

        /**
         * Применяем настройки цены профиля проекта к стоимости товара
         */
        if(false === empty($this->project_discount))
        {
            $price->applyString($this->project_discount);
        }

        return $price;
    }

    public function getProductOldPrice(): Money|false
    {
        if(empty($this->product_old_price))
        {
            return false;
        }

        $oldPrice = new Money($this->product_old_price, true);

        /**
         * Применяем настройки цены профиля проекта к стоимости товара
         */
        if(false === empty($this->project_discount))
        {
            $oldPrice->applyString($this->project_discount);
        }

        return $oldPrice;
    }

    public function getProductCurrency(): Currency
    {
        return new Currency($this->product_currency);
    }

    /**
     * @depricate Переделать расчет наличия по необработанным заказам и остаткам склада
     */
    public function getProductQuantity(): int
    {
        if(empty($this->product_quantity))
        {
            return 0;
        }

        return max($this->product_quantity, 0);
    }

    public function getArticle(): string|false
    {
        return $this->article ?: false;
    }

    public function getBarcode(): string|false
    {
        return $this->barcode ?: false;
    }
}