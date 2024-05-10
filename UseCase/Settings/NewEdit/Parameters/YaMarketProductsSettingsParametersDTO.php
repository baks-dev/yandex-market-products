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

namespace BaksDev\Yandex\Market\Products\UseCase\Settings\NewEdit\Parameters;

use BaksDev\Products\Category\Type\Section\Field\Id\CategoryProductSectionFieldUid;
use BaksDev\Yandex\Market\Products\Entity\Settings\Parameters\YaMarketProductsSettingsParametersInterface;
use BaksDev\Yandex\Market\Products\Entity\Settings\Property\YaMarketProductsSettingsPropertyInterface;
use ReflectionProperty;
use Symfony\Component\Validator\Constraints as Assert;

/** @see YaMarketProductPropertyListeners */
final class YaMarketProductsSettingsParametersDTO implements YaMarketProductsSettingsParametersInterface
{
    /**
     * Наименование характеристики
     */
    #[Assert\NotBlank]
    private ?string $name = null;

    /**
     * Тип характеристики
     */
    #[Assert\NotBlank]
    private ?string $type = null;


    /**
     * Тип характеристики
     */
    private ?array $help = null;

    /**
     * Связь на свойство продукта в категории
     */
    #[Assert\Uuid]
    private ?CategoryProductSectionFieldUid $field = null;


    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
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
     * Name
     */
    public function getName(): ?string
    {
        return $this->name ?: $this->type;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Help
     */
    public function getHelp(): ?array
    {
        return $this->help;
    }

    public function setHelp(?array $help): self
    {
        $this->help = $help;
        return $this;
    }



}