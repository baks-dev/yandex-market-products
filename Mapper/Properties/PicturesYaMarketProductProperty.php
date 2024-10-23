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

namespace BaksDev\Yandex\Market\Products\Mapper\Properties;

use BaksDev\Yandex\Market\Products\Mapper\Properties\Collection\YaMarketProductPropertyInterface;
use BaksDev\Yandex\Market\Products\Repository\Card\CurrentYaMarketProductsCard\YaMarketProductsCardInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AutoconfigureTag('baks.ya.product.property')]
final class PicturesYaMarketProductProperty implements YaMarketProductPropertyInterface
{
    /**
     * Характеристики, которые есть только у товаров конкретной категории
     */
    public const PARAM = 'pictures';


    public function __construct(
        #[Autowire(env: 'HOST')] private readonly ?string $HOST = null,
        #[Autowire(env: 'CDN_HOST')] private readonly ?string $CDN_HOST = null,
        private readonly ?YaMarketProductsCardInterface $yaMarketProductsCard = null
    ) {}


    public function getIndex(): string
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


    public function getData(array $data): mixed
    {
        if(isset($data['product_images']))
        {
            $images = json_decode($data['product_images']);

            /* Сортируем коллекцию изображений по root */
            usort($images, static function($item1) {
                return ($item1->product_img_root === false) ? 1 : -1;
            });

            foreach($images as $image)
            {
                $picture = 'https://'.($image->product_img_cdn ? $this->CDN_HOST : $this->HOST).$image->product_img.'/'.($image->product_img_cdn ? 'large.' : 'image.').$image->product_img_ext;

                // Проверяе м доступность файла изображения
                $Headers = get_headers($picture);
                $Headers = current($Headers);

                if(str_contains($Headers, '200')) // ожидаем HTTP/1.1 200 OK
                {
                    $pictures[] = $picture;
                }
            }
        }

        return $pictures ?? null;
    }
}
