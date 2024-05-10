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

declare(strict_types=1);

namespace BaksDev\Yandex\Market\Products\UseCase\Cards\NewEdit\Property;

use BaksDev\Yandex\Market\Products\Entity\Card\Property\YaMarketProductsCardPropertyInterface;
use BaksDev\Yandex\Market\Products\Type\Settings\Property\YaMarketProductProperty;
use Symfony\Component\Validator\Constraints as Assert;

/** @see YaMarketProductsCardProperty */
final class YaMarketProductsCardPropertyDTO implements YaMarketProductsCardPropertyInterface
{

    /**
     * Свойство карточки
     */
    #[Assert\NotBlank]
    private YaMarketProductProperty $type;

    /**
     * Пользовательское значение
     */
    private string $value;

    /**
     * Type
     */
    public function getType(): YaMarketProductProperty
    {
        return $this->type;
    }

    public function setType(YaMarketProductProperty $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Value
     */
    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;
        return $this;
    }
}