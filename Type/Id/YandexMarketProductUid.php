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

namespace BaksDev\Yandex\Market\Products\Type\Id;

use BaksDev\Core\Type\UidType\Uid;
use Symfony\Component\Uid\AbstractUid;

final class YandexMarketProductUid extends Uid
{
    /**
     * Тестовый идентификатор
     */
    public const string TEST = '01968062-50ae-7810-b6a6-c5c46d57be9b';

    public const string TYPE = 'yandex_market_product';

    public function __construct(
        AbstractUid|string|null $value = null,
        private readonly mixed $attr = null,
        private readonly mixed $option = null,
        private readonly mixed $property = null,
        private readonly mixed $characteristic = null,
    )
    {
        parent::__construct($value);
    }

    public function getAttr(): mixed
    {
        return $this->attr;
    }

    public function getOption(): mixed
    {
        return $this->option;
    }

    public function getProperty(): mixed
    {
        return $this->property;
    }

    public function getCharacteristic(): mixed
    {
        return $this->characteristic;
    }
}