<?php
/*
 *  Copyright 2023.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Yandex\Market\Products\Type\Settings\Property\Properties;

use BaksDev\Yandex\Market\Products\Repository\Card\CurrentYaMarketProductsCard\YaMarketProductsCardInterface;
use BaksDev\Yandex\Market\Products\Type\Card\Id\YaMarketProductsCardUid;
use BaksDev\Yandex\Market\Products\Type\Settings\Property\Properties\Collection\YaMarketProductPropertyInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('baks.ya.product.property')]
final class NameYaMarketProductProperty implements YaMarketProductPropertyInterface
{
    /**
     * Составляйте название по схеме: тип + бренд или производитель + модель + особенности, если есть (например, цвет, размер или вес) и количество в упаковке.
     */
    public const PARAM = 'name';

    private ?YaMarketProductsCardInterface $yaMarketProductsCard;

    public function __construct(?YaMarketProductsCardInterface $yaMarketProductsCard = null)
    {
        $this->yaMarketProductsCard = $yaMarketProductsCard;
    }

    public function getValue(): string
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
        return 895;
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
        return true;
    }

    public function getData(YaMarketProductsCardUid|array $card): mixed
    {
        if($this->yaMarketProductsCard)
        {
            $data = $card instanceof YaMarketProductsCardUid ? $this->yaMarketProductsCard->findByCard($card) : $card;


            $name = $data['product_name'];

            if($data['product_variation_value'])
            {
                $name .= ' '.$data['product_variation_value'];
            }

            if($data['product_modification_value'])
            {
                /** TODO: временно только для шин! */
                $name .= '/'.$data['product_modification_value'];
            }

            if($data['product_offer_value'])
            {
                /** TODO: временно только для шин! */
                $name .= ' R'.$data['product_offer_value'];
            }

            if($data['product_offer_postfix'])
            {
                $name .= ' '.$data['product_offer_postfix'];
            }

            if($data['product_variation_postfix'])
            {
                $name .= ' '.$data['product_variation_postfix'];
            }

            if($data['product_modification_postfix'])
            {
                $name .= ' '.$data['product_modification_postfix'];
            }

            return $name;
        }

        return null;
    }
}