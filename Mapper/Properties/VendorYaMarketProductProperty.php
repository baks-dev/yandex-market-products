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
use BaksDev\Yandex\Market\Products\Repository\Card\CurrentYaMarketProductsCard\CurrentYaMarketProductCardResult;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('baks.ya.product.property')]
final class VendorYaMarketProductProperty implements YaMarketProductPropertyInterface
{
    /**
     * Название бренда или производителя.
     * Должно быть записано так, как его пишет сам бренд.
     */
    public const string PARAM = 'vendor';

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
        return 891;
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
        return true;
    }

    public function isCard(): bool
    {
        return true;
    }

    public function getData(CurrentYaMarketProductCardResult $data): mixed
    {
        if(false === $data->getProductProperties())
        {
            return null;
        }

        $filter = array_filter($data->getProductProperties(), static function($element) {
            return self::equals($element->type);
        });

        $filter = current($filter);

        if($filter && $filter->value)
        {
            return $filter->value;
        }

        /** Если не найден параметр - возвращаем название категории */
        if($data->getCategoryName())
        {
            return $data->getCategoryName();
        }

        return null;
    }
}
