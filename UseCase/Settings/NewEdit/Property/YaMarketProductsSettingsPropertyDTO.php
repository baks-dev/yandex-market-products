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

namespace BaksDev\Yandex\Market\Products\UseCase\Settings\NewEdit\Property;

use BaksDev\Products\Category\Type\Section\Field\Id\CategoryProductSectionFieldUid;
use BaksDev\Yandex\Market\Products\Entity\Settings\Property\YaMarketProductsSettingsPropertyInterface;
use BaksDev\Yandex\Market\Products\Type\Settings\Property\YaMarketProductProperty;
use ReflectionProperty;
use Symfony\Component\Validator\Constraints as Assert;

/** @see YaMarketProductsSettingsProperty */
final class YaMarketProductsSettingsPropertyDTO implements YaMarketProductsSettingsPropertyInterface
{
    /**
     * Тип характеристики (Параметр в запросе на апи)
     */
    #[Assert\NotBlank]
    private ?YaMarketProductProperty $type = null;

    /**
     * Связь на свойство продукта в категории
     */
    #[Assert\Uuid]
    private ?CategoryProductSectionFieldUid $field = null;

    /**
     * По умолчанию
     */
    private ?string $def = null;


    public function getType(): ?YaMarketProductProperty
    {
        return $this->type;
    }


    public function setType(YaMarketProductProperty $type): void
    {
        $this->type = $type;
    }


    public function getField(): ?CategoryProductSectionFieldUid
    {
        return $this->field;
    }


    public function setField(?CategoryProductSectionFieldUid $field): void
    {
        $this->field = $field;
    }

    /**
     * Default
     */
    public function getDef(): ?string
    {
        return $this->def;
    }

    public function setDef(?string $default): self
    {
        $this->def = $default;
        return $this;
    }

}