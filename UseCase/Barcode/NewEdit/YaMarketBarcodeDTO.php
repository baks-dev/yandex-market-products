<?php
/*
 *  Copyright 2026.  Baks.dev <admin@baks.dev>
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
 *
 */

declare(strict_types=1);

namespace BaksDev\Yandex\Market\Products\UseCase\Barcode\NewEdit;

use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Yandex\Market\Products\Entity\Barcode\Event\YaMarketBarcodeEventInterface;
use BaksDev\Yandex\Market\Products\Type\Barcode\Event\YaMarketBarcodeEventUid;
use BaksDev\Yandex\Market\Products\UseCase\Barcode\NewEdit\Counter\YaMarketBarcodeCounterDTO;
use BaksDev\Yandex\Market\Products\UseCase\Barcode\NewEdit\Modification\YaMarketBarcodeModificationDTO;
use BaksDev\Yandex\Market\Products\UseCase\Barcode\NewEdit\Name\YaMarketBarcodeNameDTO;
use BaksDev\Yandex\Market\Products\UseCase\Barcode\NewEdit\Offer\YaMarketBarcodeOfferDTO;
use BaksDev\Yandex\Market\Products\UseCase\Barcode\NewEdit\Variation\YaMarketBarcodeVariationDTO;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/** @see YaMarketBarcodeEvent */
final class YaMarketBarcodeDTO implements YaMarketBarcodeEventInterface
{
    /**
     * Идентификатор события
     */
    #[Assert\Uuid]
    private ?YaMarketBarcodeEventUid $id = null;


    /**
     * Идентификатор категории
     */
    #[Assert\Uuid]
    #[Assert\NotBlank]
    private ?CategoryProductUid $main = null;

    /**
     * Флаг для запрета редактирования категории
     */
    private bool $hidden = false;


    /**
     * Количество стикеров
     */
    #[Assert\Valid]
    private YaMarketBarcodeCounterDTO $counter;

    /**
     * Свойства товара
     */
    #[Assert\Valid]
    private ArrayCollection $property;

    /**
     * Пользовательские свойства
     */
    #[Assert\Valid]
    private ArrayCollection $custom;

    /**
     * Флаг отображения звания в стикере
     */
    private YaMarketBarcodeNameDTO $name;


    /**
     * Добавить Торговое предложение в стикер
     */
    #[Assert\Valid]
    private YaMarketBarcodeOfferDTO $offer;

    /**
     * Добавить Множественный вариант в стикер
     */
    #[Assert\Valid]
    private YaMarketBarcodeVariationDTO $variation;

    /**
     * Добавить Модификатор множественного варианта
     */
    #[Assert\Valid]
    private YaMarketBarcodeModificationDTO $modification;


    public function __construct()
    {
        $this->property = new ArrayCollection();
        $this->custom = new ArrayCollection();

        $this->counter = new YaMarketBarcodeCounterDTO();
        $this->name = new YaMarketBarcodeNameDTO();
        $this->offer = new YaMarketBarcodeOfferDTO();
        $this->variation = new YaMarketBarcodeVariationDTO();
        $this->modification = new YaMarketBarcodeModificationDTO();
    }

    public function getEvent(): ?YaMarketBarcodeEventUid
    {
        return $this->id;
    }

    public function setId(YaMarketBarcodeEventUid $id): void
    {
        $this->id = $id;
    }


    public function getMain(): ?CategoryProductUid
    {
        return $this->main;
    }


    public function setMain(string|CategoryProductUid $category): void
    {

        $this->main = $category instanceof CategoryProductUid ? $category : new CategoryProductUid($category);
    }

    public function hiddenCategory(): void
    {
        $this->hidden = true;
    }

    public function isHiddenCategory(): bool
    {
        return $this->hidden;
    }


    public function getProperty(): ArrayCollection
    {
        return $this->property;
    }

    public function addProperty(Property\YaMarketBarcodePropertyDTO $property): void
    {
        $this->property->add($property);
    }

    public function removeProperty(Property\YaMarketBarcodePropertyDTO $property): void
    {
        $this->property->removeElement($property);
    }

    public function getPropertyClass(): Property\YaMarketBarcodePropertyDTO
    {
        return new Property\YaMarketBarcodePropertyDTO();
    }

    public function getCounter(): YaMarketBarcodeCounterDTO
    {
        return $this->counter;
    }

    /** CUSTOM */

    public function getCustom(): ArrayCollection
    {
        return $this->custom;
    }

    public function addCustom(Custom\YaMarketBarcodeCustomDTO $custom): void
    {
        $this->custom->add($custom);
    }

    public function removeCustom(Custom\YaMarketBarcodeCustomDTO $custom): void
    {
        $this->custom->removeElement($custom);
    }

    public function getName(): YaMarketBarcodeNameDTO
    {
        return $this->name;
    }

    public function getOffer(): YaMarketBarcodeOfferDTO
    {
        return $this->offer;
    }

    public function getVariation(): YaMarketBarcodeVariationDTO
    {
        return $this->variation;
    }

    public function getModification(): YaMarketBarcodeModificationDTO
    {
        return $this->modification;
    }
}