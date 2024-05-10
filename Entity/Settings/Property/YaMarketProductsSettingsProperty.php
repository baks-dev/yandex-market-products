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

namespace BaksDev\Yandex\Market\Products\Entity\Settings\Property;


use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Products\Category\Type\Section\Field\Id\CategoryProductSectionFieldUid;
use BaksDev\Yandex\Market\Products\Entity\Settings\Event\YaMarketProductsSettingsEvent;
use BaksDev\Yandex\Market\Products\Type\Settings\Property\YaMarketProductProperty;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

/* Property */

#[ORM\Entity]
#[ORM\Table(name: 'ya_market_products_settings_property')]
class YaMarketProductsSettingsProperty extends EntityEvent
{
    /**
     * Идентификатор события
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: YaMarketProductsSettingsEvent::class, inversedBy: 'property')]
    #[ORM\JoinColumn(name: 'event', referencedColumnName: 'id')]
    private YaMarketProductsSettingsEvent $event;
    
    /**
     * Наименование характеристики
     */
    #[Assert\NotBlank]
    #[ORM\Id]
    #[ORM\Column(type: YaMarketProductProperty::TYPE)]
    private YaMarketProductProperty $type;
    
    /**
     * Связь на свойство продукта в категории
     */
    #[Assert\Uuid]
    #[ORM\Column(type: CategoryProductSectionFieldUid::TYPE, nullable: true)]
    private ?CategoryProductSectionFieldUid $field = null;

    /**
     * Значение по умолчанию
     */
    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $def = null;


    public function __construct(YaMarketProductsSettingsEvent $event) {
        $this->event = $event;
    }

    public function __toString(): string
    {
        return $this->event.' '.$this->type;
    }

    public function getDto($dto): mixed
    {
        $dto = is_string($dto) && class_exists($dto) ? new $dto() : $dto;
        
        if($dto instanceof YaMarketProductsSettingsPropertyInterface)
        {
            return parent::getDto($dto);
        }
        
        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }
    

    public function setEntity($dto): mixed
    {
        if($dto instanceof YaMarketProductsSettingsPropertyInterface || $dto instanceof self)
        {
            if(empty($dto->getField()) && empty($dto->getDef())) { return false; }
            
            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }
}