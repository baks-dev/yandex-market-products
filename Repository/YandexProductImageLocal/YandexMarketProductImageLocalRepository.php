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

namespace BaksDev\Yandex\Market\Products\Repository\YandexProductImageLocal;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Yandex\Market\Products\Entity\Custom\Images\YandexMarketProductCustomImage;
use BaksDev\Yandex\Market\Products\Entity\Custom\YandexMarketProductCustom;
use Generator;


final class YandexMarketProductImageLocalRepository implements YandexMarketProductImageLocalInterface
{
    public function __construct(private readonly DBALQueryBuilder $DBALQueryBuilder) {}

    /**
     * Метод возвращает все файлы изображений, расположенных локально (CDN = FALSE)
     */
    public function findAll(): Generator
    {
        $dbal = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $dbal->from(YandexMarketProductCustom::class, 'custom');

        $dbal
            ->select('image.id')
            ->addSelect('image.name')
            ->join('custom',
                YandexMarketProductCustomImage::class,
                'image',
                'image.invariable = custom.invariable AND image.cdn IS NOT TRUE',
            );

        return $dbal->fetchAllHydrate(YandexMarketProductImageLocalResult::class);
    }
}