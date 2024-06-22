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
final class AgeYaMarketProductProperty implements YaMarketProductPropertyInterface
{
    /**
     * Если товар не предназначен для детей младше определенного возраста, укажите это.
     *
     * Возрастное ограничение можно задавать в годах (с нуля, с 6, 12, 16 или 18)
     * или в месяцах (любое число от 0 до 12).
     */
    public const PARAM = 'age';

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
        return 400;
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
        return true;
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

            if(isset($data['product_propertys']))
            {
                $property = json_decode($data['product_propertys']);

                $filter = current(array_filter($property, function ($element) {
                    return self::equals($element->type);
                }));

                if($filter && $filter->value)
                {
                    return $filter->value;
                }
            }
        }

        return null;
    }
}
