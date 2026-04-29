<?php
/*
 *  Copyright 2026.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Yandex\Market\Products\Repository\Barcode\AllYaMarketBarcodeSettings;

use BaksDev\Products\Category\Type\Event\CategoryProductEventUid;
use BaksDev\Yandex\Market\Products\Type\Barcode\Event\YaMarketBarcodeEventUid;

final readonly class AllYaMarketBarcodeSettingsResult
{
    public function __construct(
        private string $event,
        private string $category_event,

        private ?string $category_cover_img,
        private ?string $category_cover_ext,
        private ?bool $category_cover_cdn,

        private string $category_name,
        private string $category_description,
    ) {}


    public function getEvent(): YaMarketBarcodeEventUid
    {
        return new YaMarketBarcodeEventUid($this->event);
    }

    public function getCategoryEvent(): CategoryProductEventUid
    {
        return new CategoryProductEventUid($this->category_event);
    }

    public function getCategoryCoverImg(): ?string
    {
        return $this->category_cover_img;
    }

    public function getCategoryCoverExt(): ?string
    {
        return $this->category_cover_ext;
    }

    public function getCategoryCoverCdn(): ?bool
    {
        return $this->category_cover_cdn;
    }

    public function getCategoryName(): string
    {
        return $this->category_name;
    }

    public function getCategoryDescription(): string
    {
        return $this->category_description;
    }
}