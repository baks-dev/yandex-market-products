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

namespace BaksDev\Yandex\Market\Products\Mapper\Properties;

use BaksDev\Yandex\Market\Products\Mapper\Properties\Collection\YaMarketProductPropertyInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Uid\UuidV7;

#[AutoconfigureTag('baks.ya.product.property')]
final class BarcodesCodeYaMarketProductProperty implements YaMarketProductPropertyInterface
{
    /**
     * Указывайте в виде последовательности цифр.
     * Подойдут коды EAN-13, EAN-8, UPC-A, UPC-E или Code 128.
     *
     * Внутренние штрихкоды, начинающиеся на 2 или 02, и коды формата Code 128 не являются GTIN.
     */
    public const string PARAM = 'barcodes';

    public function getIndex(): string
    {
        return self::PARAM;
    }

    public function default(): ?string
    {
        return null;
    }

    public function required(): bool
    {
        return false;
    }

    public function choices(): ?array
    {
        return null;
    }

    /**
     * Сортировка (чем меньше число - тем первым в итерации будет значение)
     */
    public static function priority(): int
    {
        return 890;
    }

    /**
     * Проверяет, относится ли статус к данному объекту
     */
    public static function equals(string $status): bool
    {
        return self::PARAM === $status;
    }


    public function isSetting(): bool
    {
        return false;
    }

    public function isCard(): bool
    {
        return false;
    }

    public function getData(array $data): mixed
    {
        if(!empty($data['barcode']))
        {
            return $data['barcode'];
        }

        $return_value = match (true)
        {
            isset($data['modification_const']) => new UuidV7($data['modification_const']),
            isset($data['variation_const']) => new UuidV7($data['variation_const']),
            isset($data['offer_const']) => new UuidV7($data['offer_const']),
            isset($data['product_const']) => new UuidV7($data['product_const']),
            default => null,
        };

        if($return_value)
        {
            $numbers = filter_var((string) $return_value, FILTER_SANITIZE_NUMBER_INT);

            $numbers = explode('-', $numbers);
            $numbers = array_map('intval', $numbers);
            $numbers = array_sum($numbers);

            return ['0461'.substr((string) $return_value->getDateTime()->getTimestamp(), 2).substr((string) $numbers, 0, 2)];
        }

        return null;
    }
}
