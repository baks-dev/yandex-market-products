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
final class SpeedIndexYaMarketProductParams implements YaMarketProductParamsInterface
{
    public const int CATEGORY = 90490;

    public const int ID = 50322776;

    public function getName(): string
    {
        return 'Индекс максимальной скорости';
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

        return in_array($param, [(string) self::ID, 'индекс максимальной скорости', 'индекс скорости'], true);
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
        if($data->getProductModificationPostfix())
        {
            $cleaned_str = preg_replace('/[^a-zA-Z]/u', '', $data->getProductModificationPostfix());

            /** Преобразуем русские символы в латиницу */
            $cleaned_str = str_replace(
                ['Н', 'К', 'М', 'Р', 'Т'], // русские символы
                ['H', 'K', 'M', 'P', 'T'], // латиница
                $cleaned_str
            );

            if(!empty($cleaned_str))
            {
                $return_index = match ($cleaned_str)
                {
                    'H' => 'H (до 210 км/ч)',
                    'J' => 'J (до 100 км/ч)',
                    'K' => 'K (до 110 км/ч)',
                    'L' => 'L (до 120 км/ч)',
                    'M' => 'M (до 130 км/ч)',
                    'N' => 'N (до 140 км/ч)',
                    'P' => 'P (до 150 км/ч)',
                    'Q' => 'Q (до 160 км/ч)',
                    'R' => 'R (до 170 км/ч)',
                    'S' => 'S (до 180 км/ч)',
                    'T' => 'T (до 190 км/ч)',
                    'V' => 'V (до 240 км/ч)',
                    'W' => 'W (до 270 км/ч)',
                    'Y' => 'Y (до 300 км/ч)',
                    'Z/ZR' => 'Z/ZR (свыше 240 км/ч)'
                };

                return [
                    'parameterId' => $this::ID,
                    'name' => $this->getName(),
                    'value' => $return_index
                ];
            }
        }

        return null;
    }
}
