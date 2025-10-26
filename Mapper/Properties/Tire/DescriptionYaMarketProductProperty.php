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

use BaksDev\Yandex\Market\Products\Mapper\Params\Tire\PurposeYaMarketProductParams;
use BaksDev\Yandex\Market\Products\Mapper\Params\Tire\SeasonYaMarketProductParams;
use BaksDev\Yandex\Market\Products\Mapper\Properties\Collection\YaMarketProductPropertyInterface;
use BaksDev\Yandex\Market\Products\Repository\Card\CurrentYaMarketProductsCard\CurrentYaMarketProductCardResult;
use BaksDev\Yandex\Market\Products\Type\Settings\Property\YaMarketProductProperty;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('baks.ya.product.property')]
final class DescriptionYaMarketProductProperty implements YaMarketProductPropertyInterface
{
    /**
     * Подробное описание товара: например, его преимущества и особенности.
     */
    public const string PARAM = 'description';


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
        return 894;
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
        return true;
    }

    public function getData(CurrentYaMarketProductCardResult $data): ?string
    {

        if($data->getMarketCategory() !== YaMarketProductProperty::CATEGORY_TIRE)
        {
            return null;
        }

        $name = '';

        if($data->getProductParams() !== false)
        {
            /** Добавляем к названию сезонность */
            $Season = new SeasonYaMarketProductParams();

            foreach($data->getProductParams() as $product_param)
            {
                if($Season->equals($product_param->name))
                {
                    $season_value = $Season->getData($data);

                    if(!empty($season_value['value']))
                    {
                        $name .= $season_value['value'].' ';
                    }
                }
            }
        }

        $name .= 'шины ';


        /** Приводим к нижнему регистру и первой заглавной букве */
        $name = mb_strtolower(trim($name));
        $firstChar = mb_substr($name, 0, 1, 'UTF-8');
        $then = mb_substr($name, 1, null, 'UTF-8');
        $name = mb_strtoupper($firstChar, 'UTF-8').$then.' ';


        $name .= $data->getProductName().' ';

        if($data->getProductVariationValue())
        {
            $name .= $data->getProductVariationValue();
        }

        if($data->getProductModificationValue())
        {
            $name .= '/'.$data->getProductModificationValue().' ';
        }

        if($data->getProductOfferValue())
        {
            $name .= 'R'.$data->getProductOfferValue().' ';
        }

        if($data->getProductOfferPostfix())
        {
            $name .= $data->getProductOfferPostfix().' ';
        }

        if($data->getProductVariationPostfix())
        {
            $name .= $data->getProductVariationPostfix().' ';
        }

        if($data->getProductModificationPostfix())
        {
            $name .= $data->getProductModificationPostfix().' ';
        }


        if($data->getProductParams() !== false)
        {
            /** Добавляем к названию назначение */
            $Purpose = new PurposeYaMarketProductParams();

            foreach($data->getProductParams() as $product_param)
            {
                if($Purpose->equals($product_param->name))
                {
                    $purpose_value = $Purpose->getData($data);

                    if(!empty($purpose_value['value']))
                    {
                        $name .= ', '.$purpose_value['value'].' ';
                    }
                }
            }
        }


        $name .= '<br>';
        $name .= $data->getProductPreview();

        if($data->getProductVariationValue())
        {
            $name .= '<br>';
            $name .= sprintf('Ширина профиля %s обеспечивает надежное сцепление с дорогой и стабильность на поворотах.', $data->getProductVariationValue());
        }

        if($data->getProductModificationValue())
        {
            $name .= '<br>';
            $name .= sprintf('Высота профиля %s обеспечивает оптимальное сочетание комфорта и управляемости.', $data->getProductModificationValue());
        }

        return empty($name) ? null : trim($name);

    }
}
