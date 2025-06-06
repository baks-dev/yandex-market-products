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

namespace BaksDev\Yandex\Market\Products\Mapper\Properties\Tire;

use BaksDev\Yandex\Market\Products\Mapper\Properties\Collection\YaMarketProductPropertyInterface;
use BaksDev\Yandex\Market\Products\Repository\Card\CurrentYaMarketProductsCard\CurrentYaMarketProductCardResult;
use BaksDev\Yandex\Market\Products\Type\Settings\Property\YaMarketProductProperty;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('baks.ya.product.property')]
final class ShelfLifeYaMarketProductProperty implements YaMarketProductPropertyInterface
{
    /**
     * Срок годности — период, по прошествии которого товар становится непригоден.
     *
     * Указывайте срок, указанный на банке или упаковке. Текущая дата, дата поставки или дата отгрузки значения не
     * имеет.
     *
     * Обязательно указывайте срок, если он есть.
     *
     * В комментарии укажите условия хранения. Например, «Хранить в сухом помещении».
     */
    public const string PARAM = 'shelfLife';

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
        return 500;
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

    public function getData(CurrentYaMarketProductCardResult $data): ?array
    {
        if($data->getMarketCategory() !== YaMarketProductProperty::CATEGORY_TIRE)
        {
            return null;
        }

        return [
            'timePeriod' => 5,
            'timeUnit' => 'YEAR',
            'comment' => 'Допустимые температуры от -30 до +35 без резких перепадов; помещение сухое, затемненное, хорошо вентилируемое; исключить контакт с материалами, подверженными коррозии, со смазочными, топливными материалов, щелочами, кислотами'];

    }
}
