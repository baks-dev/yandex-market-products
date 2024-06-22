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

namespace BaksDev\Yandex\Market\Products\Type\Settings\Property\Properties;

use BaksDev\Yandex\Market\Products\Repository\Card\CurrentYaMarketProductsCard\YaMarketProductsCardInterface;
use BaksDev\Yandex\Market\Products\Type\Card\Id\YaMarketProductsCardUid;
use BaksDev\Yandex\Market\Products\Type\Settings\Property\Properties\Collection\YaMarketProductPropertyInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Uid\UuidV7;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AutoconfigureTag('baks.ya.product.property')]
final class ParamsYaMarketProductProperty implements YaMarketProductPropertyInterface
{
    /**
     * Характеристики, которые есть только у товаров конкретной категории
     */
    public const PARAM = 'params';

    public function __construct(
        private readonly ?YaMarketProductsCardInterface $yaMarketProductsCard = null,
        private readonly ?TranslatorInterface $translator = null
    ) {}

    public function getValue(): string
    {
        return self::PARAM;
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

    public function getData(YaMarketProductsCardUid|array $card): mixed
    {
        if($this->yaMarketProductsCard)
        {
            $data = $card instanceof YaMarketProductsCardUid ? $this->yaMarketProductsCard->findByCard($card) : $card;

            if(isset($data['product_params']))
            {

                $product_params = json_decode($data['product_params'], false);

                /** Tansform data */

                foreach($product_params as $param)
                {
                    if($param->value === 'false')
                    {
                        continue;
                    }

                    /** Категория ШИНЫ */
                    if($data["market_category"] === 90490)
                    {
                        if($param->name === 'Диаметр')
                        {
                            $param->value = $this->translator->trans($param->value, domain: 'field.tire.radius');

                        }

                        if($param->name === 'Сезонность')
                        {
                            $param->value = $this->translator->trans($param->value, domain: 'field.tire.season');

                        }

                        if($param->name === 'Класс топливной эффективности')
                        {
                            if(!$param->value)
                            {
                                continue;
                            }

                            $arr = json_decode($param->value);

                            if(!isset($arr[0]))
                            {
                                continue;
                            }

                            $param->value = $arr[0];
                        }

                        if($param->name === 'Сцепление на мокрой дороге')
                        {
                            if(!$param->value)
                            {
                                continue;
                            }

                            $arr = json_decode($param->value);

                            if(!isset($arr[1]))
                            {
                                continue;
                            }

                            $param->value = $arr[1];
                        }

                        if($param->name === 'Уровень внешнего шума')
                        {
                            if(!$param->value)
                            {
                                continue;
                            }

                            $arr = json_decode($param->value);

                            if(!isset($arr[2]))
                            {
                                continue;
                            }

                            $param->value = $arr[2];

                        }

                        if($param->name === 'Назначение')
                        {
                            $param->value = $this->translator->trans($param->value.'_desc', domain: 'field.tire.cartype');
                        }

                    }

                    $params[] = [
                        'name' => $param->name,
                        'value' => $param->value
                    ];
                }



                if($data['product_modification_postfix'])
                {

                    $cleaned_str = preg_replace('/[^a-zA-Z]/u', '', $data['product_modification_postfix']);

                    if(!empty($cleaned_str))
                    {
                        $params[] = [
                            'name' => 'Максимальная скорость - индекс',
                            'value' => $cleaned_str
                        ];


                        /** Индекс максимальной скорости */

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

                        $params[] = [
                            'name' => 'Индекс максимальной скорости',
                            'value' => $return_index
                        ];

                    }


                    $index = explode('/', $data['product_modification_postfix']);
                    $cleaned_int = filter_var(current($index), FILTER_SANITIZE_NUMBER_INT);

                    if(!empty($cleaned_int))
                    {
                        $params[] = [
                            'name' => 'Индекс нагрузки',
                            'value' => (int) $cleaned_int
                        ];
                    }
                }

                $params[] = [
                    'name' => 'Омологация',
                    'value' => 'отсутствует'
                ];

                /** Объединяем карточки по идентификатору */
                $params[] = [
                    'name' => 'Название группы вариантов',
                    'value' => $data['product_card']
                ];

                /*if(isset($data['product_uid']))
                {
                    $return_value = new UuidV7($data['product_uid']);

                    if($return_value)
                    {
                        $numbers = filter_var((string) $return_value, FILTER_SANITIZE_NUMBER_INT);

                        $numbers = explode('-', $numbers);
                        $numbers = array_map('intval', $numbers);
                        $numbers = array_sum($numbers);

                        $numberCard = filter_var((string) $data['product_card'], FILTER_SANITIZE_NUMBER_INT);

                        $identifier = (int) substr($numbers.$numberCard, 0, 64);

                        $params[] = [
                            'name' => 'Название группы вариантов',
                            'value' => $data['product_card']
                        ];

                    }
                }*/

            }
        }

        return $params ?? null;
    }

}