<?php
/*
 *  Copyright 2022.  Baks.dev <admin@baks.dev>
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *   limitations under the License.
 *
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

    /** Категория Wildberries */
    #[Assert\NotBlank]
    private string $name;


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
        $this->property = new ArrayCollection();
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


    /**
     * Категория Wildberries
     */
    public function getName(): string
    {
        return $this->name;
    }


    public function setName(string $name): void
    {
        $this->name = $name;
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


    /* PROPERTY */

    public function getProperty(): ArrayCollection
    {
        return $this->property;
    }


    public function addProperty(Property\YaMarketProductsSettingsPropertyDTO $property): void
    {
        $this->property->add($property);
    }


    public function removeProperty(Property\YaMarketProductsSettingsPropertyDTO $property): void
    {
        $this->property->removeElement($property);
    }


}