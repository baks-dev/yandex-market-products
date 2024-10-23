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

namespace BaksDev\Yandex\Market\Products\Entity\Settings\Event;


use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Yandex\Market\Products\Entity\Settings\Modify\YaMarketProductsSettingsModify;
use BaksDev\Yandex\Market\Products\Entity\Settings\Parameters\YaMarketProductsSettingsParameters;
use BaksDev\Yandex\Market\Products\Entity\Settings\Property\YaMarketProductsSettingsProperty;
use BaksDev\Yandex\Market\Products\Entity\Settings\YaMarketProductsSettings;
use BaksDev\Yandex\Market\Products\Type\Settings\Event\YaMarketProductsSettingsEventUid;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

/* Event */

#[ORM\Entity]
#[ORM\Table(name: 'ya_market_products_settings_event')]
//#[ORM\Index(columns: ['settings'])]
    //#[ORM\Index(columns: ['market'])]
class YaMarketProductsSettingsEvent extends EntityEvent
{
    /**
     * Идентификатор события
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\Column(type: YaMarketProductsSettingsEventUid::TYPE)]
    private YaMarketProductsSettingsEventUid $id;

    /**
     * Идентификатор категории в системе
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Column(type: CategoryProductUid::TYPE)]
    private CategoryProductUid $settings;

    /**
     * Идентификатор категории на Маркете
     */
    #[Assert\NotBlank]
    #[ORM\Column(type: Types::INTEGER)]
    private int $market;


    /**
     * Модификатор
     */
    #[Assert\Valid]
    #[ORM\OneToOne(targetEntity: YaMarketProductsSettingsModify::class, mappedBy: 'event', cascade: ['all'])]
    private YaMarketProductsSettingsModify $modify;

    /**
     * Свойства карточки Маркет
     */
    #[Assert\Valid]
    #[ORM\OneToMany(targetEntity: YaMarketProductsSettingsProperty::class, mappedBy: 'event', cascade: ['all'])]
    private Collection $property;


    /**
     * Параметры продукции категории Маркет
     */
    #[Assert\Valid]
    #[ORM\OneToMany(targetEntity: YaMarketProductsSettingsParameters::class, mappedBy: 'event', cascade: ['all'])]
    private Collection $parameters;


    public function __construct()
    {
        $this->id = new YaMarketProductsSettingsEventUid();
        $this->modify = new YaMarketProductsSettingsModify($this);
    }

    public function __clone(): void
    {
        $this->id = clone $this->id;
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }

    public function getDto($dto): mixed
    {
        $dto = is_string($dto) && class_exists($dto) ? new $dto() : $dto;

        if($dto instanceof YaMarketProductsSettingsEventInterface)
        {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }


    public function setEntity($dto): mixed
    {
        if($dto instanceof YaMarketProductsSettingsEventInterface || $dto instanceof self)
        {
            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    public function setMain(YaMarketProductsSettings $settings): self
    {
        return $this;
    }

    public function getId(): YaMarketProductsSettingsEventUid
    {
        return $this->id;
    }

    public function getSettings(): CategoryProductUid
    {
        return $this->settings;
    }

}