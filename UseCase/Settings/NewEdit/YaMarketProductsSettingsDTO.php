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

namespace BaksDev\Yandex\Market\Products\UseCase\Settings\NewEdit;


use BaksDev\Products\Category\Entity\CategoryProduct;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Yandex\Market\Products\Entity\Settings\Event\YaMarketProductsSettingsEventInterface;
use BaksDev\Yandex\Market\Products\Type\Settings\Event\YaMarketProductsSettingsEventUid;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/** @see YaMarketProductsSettingsEvent */
final class YaMarketProductsSettingsDTO implements YaMarketProductsSettingsEventInterface
{
    /**
     * Идентификатор события
     */
    #[Assert\Uuid]
    private ?YaMarketProductsSettingsEventUid $id = null;


    /**
     * ID настройки
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    private readonly CategoryProductUid $settings;


    /**
     * Идентификатор категории на Маркете
     */
    #[Assert\NotBlank]
    private int $market;


    /**
     * Параметры карточки
     */
    #[Assert\Valid]
    private ArrayCollection $parameters;

    /**
     * Коллекция Свойств
     */
    #[Assert\Valid]
    private ArrayCollection $property;

    //    /** Коллекция торговых предложений */
    //    private ArrayCollection $offer;
    //
    //    /** Коллекция вариантов в торговом предложении */
    //    private ArrayCollection $variation;


    //    private ConfigCardDTO $config;
    //
    //private ?FieldUid $country = null;

    public function __construct()
    {
        $this->property = new ArrayCollection;
        $this->parameters = new ArrayCollection;
        //        $this->offer = new ArrayCollection();
        //        $this->variation = new ArrayCollection();
    }

    /**
     * Идентификатор события
     */
    public function getEvent(): ?YaMarketProductsSettingsEventUid
    {
        return $this->id;
    }


    public function setId(YaMarketProductsSettingsEventUid $id): void
    {
        $this->id = $id;
    }


    /**
     * ID настройки
     */
    public function getSettings(): ?CategoryProductUid
    {
        return $this->settings;
    }


    public function setSettings(CategoryProductUid|CategoryProduct $settings): void
    {
        $this->settings = $settings instanceof CategoryProduct ? $settings->getId() : $settings;
    }


    //    /**
    //     * Категория Wildberries
    //     */
    //    public function getName(): string
    //    {
    //        return $this->name;
    //    }
    //
    //
    //    public function setName(string $name): void
    //    {
    //        $this->name = $name;
    //    }


    /**
     * Market
     */
    public function getMarket(): int
    {
        return $this->market;
    }

    public function setMarket(int $market): self
    {
        $this->market = $market;
        return $this;
    }





    //    public function updConfigCard(array $configCard) : void
    //    {
    //
    //        /** @var CharacteristicCardDTO $current */
    //        $current = current($configCard);
    //
    //
    //        $this->name = current($configCard)-> // $configCard->getName();
    //        $this->config = $configCard;
    //        //$this->parent = $configCard->getParent();
    //
    //        /* PROPERTY */
    //
    //        $properties = $configCard->getProperty()->getIterator();
    //
    //        /* Сортируем коллекцию по обязательному заполнению */
    //        $properties->uasort(function ($first, $second) {
    //            return (int) $first->isRequired() > (int) $second->isRequired() ? -1 : 1;
    //        });
    //
    //        /** @var PropertyDTO $property */
    //        foreach($properties as $property)
    //        {
    //            if($property->getType() === 'Наименование') { continue; }
    //            if($property->getType() === 'Описание') { continue; }
    //
    //            /* Получаем элемент из коллекции по типу */
    //            $issetProperty = $this->property->filter(function( $v ) use ($property) {
    //                return $v->getType() === $property->getType();
    //            });
    //
    //            if($issetProperty->isEmpty())
    //            {
    //                $newProperty = new Property\WbProductSettingsDTO();
    //                $this->addProperty($newProperty);
    //            }
    //            else
    //            {
    //                $newProperty = $issetProperty->current();
    //            }
    //
    //            /* Обновляем вспомогательные свойства */
    //            $newProperty->updConfigCardProperty($property);
    //
    //        }
    //   }


    /* PARAMS */

    public function getParameters(): ArrayCollection
    {
        return $this->parameters;
    }


    public function addParameter(Parameters\YaMarketProductsSettingsParametersDTO $parameters): void
    {
        $filter = $this->parameters->filter(
            function(Parameters\YaMarketProductsSettingsParametersDTO $element) use ($parameters) {
                return $parameters->getType() === $element->getType();
            });

        if($filter->isEmpty())
        {

            $this->parameters->add($parameters);
        }

        else
        {
            $filter->current()
                ->setName($parameters->getName())
                ->setHelp($parameters->getHelp());
        }
    }


    public function removeParameter(Parameters\YaMarketProductsSettingsParametersDTO $parameters): void
    {
        $this->parameters->removeElement($parameters);
    }


    /* PROPERTY */

    public function getProperty(): ArrayCollection
    {
        return $this->property;
    }


    public function addProperty(Property\YaMarketProductsSettingsPropertyDTO $property): void
    {
        $filter = $this->property->filter(function(Property\YaMarketProductsSettingsPropertyDTO $element) use ($property
        ) {
            return $property->getType()?->equals($element->getType());
        });

        if($filter->isEmpty())
        {
            $this->property->add($property);
        }

    }


    public function removeProperty(Property\YaMarketProductsSettingsPropertyDTO $property): void
    {
        $this->property->removeElement($property);
    }


}