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

namespace BaksDev\Yandex\Market\Products\UseCase\Settings\NewEdit\Parameters;

use BaksDev\Products\Category\Type\Section\Field\Id\CategoryProductSectionFieldUid;
use BaksDev\Yandex\Market\Products\Entity\Settings\Parameters\YaMarketProductsSettingsParametersInterface;
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