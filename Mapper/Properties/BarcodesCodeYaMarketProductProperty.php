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

use BaksDev\Core\Type\UidType\Uid;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
use BaksDev\Yandex\Market\Products\Mapper\Properties\Collection\YaMarketProductPropertyInterface;
use BaksDev\Yandex\Market\Products\Repository\Card\CurrentYaMarketProductsCard\CurrentYaMarketProductCardResult;
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
    public static function equals(string $value): bool
    {
        return self::PARAM === $value;
    }


    public function isSetting(): bool
    {
        return false;
    }

    public function isCard(): bool
    {
        return false;
    }

    public function getData(CurrentYaMarketProductCardResult $data): mixed
    {
        if($data->getBarcode() !== false)
        {
            return [$data->getBarcode()];
        }

        /** @var UuidV7 $return_value */
        $return_value = match (true)
        {
            $data->getModificationConst() instanceof ProductModificationConst => $data->getModificationConst(),
            $data->getVariationConst() instanceof ProductVariationConst => $data->getVariationConst(),
            $data->getOfferConst() instanceof ProductOfferConst => $data->getOfferConst(),
            $data->getProductId() instanceof ProductUid => $data->getProductId(),
            default => false,
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
