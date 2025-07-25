<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Yandex\Market\Products\Mapper\Params\Tire;

use BaksDev\Yandex\Market\Products\Mapper\Params\YaMarketProductParamsInterface;
use BaksDev\Yandex\Market\Products\Repository\Card\CurrentYaMarketProductsCard\CurrentYaMarketProductCardResult;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AutoconfigureTag('baks.ya.product.params')]
final class SuvClassYaMarketProductParams implements YaMarketProductParamsInterface
{
    public const int CATEGORY = 90490;

    public const int ID = 37808670;

    public function getName(): string
    {
        return 'Класс шин для внедорожников';
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
        return 600;
    }

    /**
     * Проверяет, относится ли значение к данному объекту
     */
    public function equals(int|string $param): bool
    {
        $param = mb_strtolower((string) $param);

        return in_array($param, [(string) self::ID, mb_strtolower($this->getName())], true);
    }

    public function isSetting(): bool
    {
        return false;
    }

    public function isCard(): bool
    {
        return false;
    }

    public function getData(CurrentYaMarketProductCardResult $data, ?TranslatorInterface $translator = null): array|null
    {
        if(false === $data->getProductParams())
        {
            return null;
        }

        foreach($data->getProductParams() as $product_param)
        {
            if($this->equals($product_param->name))
            {
                if(empty($product_param->value) || $product_param->value === 'false')
                {
                    return null;
                }

                $return_index = match ($product_param->value)
                {
                    'AT' => 39065912, // AT вседорожный
                    'HP', => 39065913, // HP высокоскоростной
                    'HT', => 39065911, // HT дорожный
                    'MT', => 39065914, // MT внедорожный
                    default => false,
                };

                if($return_index)
                {
                    $return_name = match ($return_index)
                    {
                        39065912 => 'AT вседорожный',
                        39065913 => 'HP высокоскоростной',
                        39065911 => 'HT дорожный',
                        39065914 => 'MT внедорожный',
                    };

                    return [
                        'parameterId' => $this::ID,
                        'name' => $this->getName(),
                        'valueId' => $return_index,
                        'value' => $return_name,
                    ];
                }
            }
        }

        return null;
    }
}
