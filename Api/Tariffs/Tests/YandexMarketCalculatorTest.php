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

namespace BaksDev\Yandex\Market\Products\Api\Tariffs\Tests;

use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Yandex\Market\Products\Api\Tariffs\YandexMarketCalculatorRequest;
use BaksDev\Yandex\Market\Type\Authorization\YaMarketAuthorizationToken;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group yandex-market-calculator-test
 */
#[When(env: 'test')]
class YandexMarketCalculatorTest extends KernelTestCase
{
    private static YaMarketAuthorizationToken $Authorization;

    public static function setUpBeforeClass(): void
    {
        self::$Authorization = new YaMarketAuthorizationToken(
            new UserProfileUid(),
            $_SERVER['TEST_YANDEX_MARKET_TOKEN'],
            $_SERVER['TEST_YANDEX_MARKET_COMPANY'],
            $_SERVER['TEST_YANDEX_MARKET_BUSINESS']
        );
    }

    public function testUseCase(): void
    {
        /** @var YandexMarketCalculatorRequest $YandexMarketCalculatorRequest .php */
        $YandexMarketCalculatorRequest = self::getContainer()->get(YandexMarketCalculatorRequest::class);
        $YandexMarketCalculatorRequest->TokenHttpClient(self::$Authorization);

        $calc = $YandexMarketCalculatorRequest
            ->category(90490)
            ->price(10000)
            ->width(70)
            ->height(70)
            ->length(25)
            ->weight(12.5)
            ->calc();

        // DBS 10880
        // FBS 12140.0
        //dd($calc);

        self::assertIsFloat($calc);

    }

}
