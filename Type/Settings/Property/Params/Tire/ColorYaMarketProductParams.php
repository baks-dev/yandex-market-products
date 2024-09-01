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

namespace BaksDev\Yandex\Market\Products\Type\Settings\Property\Params\Tire;

use BaksDev\Yandex\Market\Products\Type\Settings\Property\Params\YaMarketProductParamsInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AutoconfigureTag('baks.ya.product.params')]
final class ColorYaMarketProductParams implements YaMarketProductParamsInterface
{
    public const int CATEGORY = 90490;

    public const int ID = 14871214;

    public function getName(): string
    {
        /** @example 185/70R14 */
        return 'Цвет товара для карточки';
    }

    public function required(): bool
    {
        return false;
    }

    public function default(): ?string
    {
        return null;
    }

    /** Массив допустимых значений */
    public function choices(): ?array
    {
        return null;
    }

    /**
     * Сортировка (чем меньше число - тем первым в итерации будет значение)
     */
    public static function priority(): int
    {
        return 610;
    }

    /**
     * Проверяет, относится ли значение к данному объекту
     */
    public function equals(int|string $param): bool
    {
        $param = mb_strtolower((string) $param);

        return in_array($param, [
            (string) self::ID,
            mb_strtolower($this->getName())
        ], true);
    }

    public function isSetting(): bool
    {
        return false;
    }

    public function isCard(): bool
    {
        return false;
    }

    public function getData(array $data, ?TranslatorInterface $translator = null): mixed
    {
        $name = '';

        if($data['product_variation_value'])
        {
            $name .= $data['product_variation_value'];
        }

        if($data['product_modification_value'])
        {
            $name .= '/'.$data['product_modification_value'];
        }

        if($data['product_offer_value'])
        {
            $name .= 'R'.$data['product_offer_value'];
        }

        if(empty($name))
        {
            return null;
        }

        return [
            'parameterId' => $this::ID,
            'name' => $this->getName(),
            'value' => $name
        ];
    }
}
