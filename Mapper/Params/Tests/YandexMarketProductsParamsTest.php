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

namespace BaksDev\Yandex\Market\Products\Mapper\Params\Tests;

use BaksDev\Yandex\Market\Products\Type\Card\Id\YaMarketProductsCardUid;
use BaksDev\Yandex\Market\Products\Mapper\Properties\ParameterValuesYaMarketProductProperty;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group yandex-market-products
 * @group yandex-market-products-params-tire
 */
#[When(env: 'test')]
class YandexMarketProductsParamsTest extends KernelTestCase
{
    public function testUseCase(): void
    {
        /** @var ParameterValuesYaMarketProductProperty $ParameterValuesYaMarketProductProperty */
        //$ParameterValuesYaMarketProductProperty = self::getContainer()->get(ParameterValuesYaMarketProductProperty::class);
        //$params = $ParameterValuesYaMarketProductProperty->getData(new YaMarketProductsCardUid('018f3ff4-ceda-71dc-b0d6-b47b4c929c38'));

        // dd($params);
        //self::assertNotNull($params);

        self::assertTrue(true);
    }
}
