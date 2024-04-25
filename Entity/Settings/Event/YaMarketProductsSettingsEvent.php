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

namespace BaksDev\Yandex\Market\Products\Entity\Settings\Event;


use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Yandex\Market\Products\Entity\Settings\Modify\YaMarketProductsSettingsModify;
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
#[ORM\Index(columns: ['settings'])]
#[ORM\Index(columns: ['name'])]
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
     * Идентификатор настройки
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Column(type: CategoryProductUid::TYPE)]
    private CategoryProductUid $settings;

    /**
     * Категория Wildberries
     */
    #[Assert\NotBlank]
    #[ORM\Column(type: Types::STRING)]
    private string $name;

    /**
     * Модификатор
     */
    #[Assert\Valid]
    #[ORM\OneToOne(targetEntity: YaMarketProductsSettingsModify::class, mappedBy: 'event', cascade: ['all'])]
    private YaMarketProductsSettingsModify $modify;

    /**
     * Свойства карточки
     */
    #[Assert\Valid]
    #[ORM\OneToMany(targetEntity: YaMarketProductsSettingsProperty::class, mappedBy: 'event', cascade: ['all'])]
    private Collection $property;

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


    public function getName(): string
    {
        return $this->name;
    }

}