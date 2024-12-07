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

namespace BaksDev\Yandex\Market\Products\Api\Reference\Parameters\Tests;

use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Yandex\Market\Products\Api\AllShops\YandexMarketShopDTO;
use BaksDev\Yandex\Market\Products\Api\AllShops\YandexMarketShopRequest;
use BaksDev\Yandex\Market\Products\Api\Reference\Parameters\YandexMarketGetParametersRequest;
use BaksDev\Yandex\Market\Products\Mapper\Params\Tire\ColorYaMarketProductParams;
use BaksDev\Yandex\Market\Products\Mapper\Params\YaMarketProductParamsCollection;
use BaksDev\Yandex\Market\Products\Mapper\Params\YaMarketProductParamsInterface;
use BaksDev\Yandex\Market\Type\Authorization\YaMarketAuthorizationToken;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group yandex-market-products
 */
#[When(env: 'test')]
final class YandexMarketParametersTireTest extends KernelTestCase
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

        self::assertTrue(true);


        /** @var YandexMarketGetParametersRequest $YandexMarketCategoryRequest */
        $YandexMarketCategoryRequest = self::getContainer()->get(YandexMarketGetParametersRequest::class);

        $YandexMarketCategoryRequest->TokenHttpClient(self::$Authorization);

        $result = $YandexMarketCategoryRequest
            ->category(90490) // 90490 - Авто - Шины и диски - Шины
            ->findAll();

        //dd(iterator_to_array($result));


        /** @var YaMarketProductParamsCollection $YaMarketProductParamsCollection */
        $YaMarketProductParamsCollection = self::getContainer()->get(YaMarketProductParamsCollection::class);


        // список параметров Шин

        $cases = $YaMarketProductParamsCollection->cases(90490);

        $params = null;

        /** @var YaMarketProductParamsInterface $case */
        foreach($cases as $case)
        {
            $params[$case::ID] = $case->getName();
        }

        // Неопределенные
        $params[7351754] = 'Дополнительная информация';
        $params[57046341] = 'Прочие характеристики';
        $params[40164890] = 'Изображение для миниатюры';

        foreach($result as $data)
        {
            self::assertTrue(isset($params[$data->getId()]), message: sprintf('Новый параметр %s: %s', $data->getId(), $data->getName()));

            if(isset($params[$data->getId()]))
            {
                unset($params[$data->getId()]);
            }
        }

        /* Лишние параметры, которые были удалены и могут привести к ошибке */
        if(!empty($params))
        {
            self::assertTrue(false, message: sprintf('Лишний параметр c идентификатором %s', implode(', ', array_keys($params))));
        }

    }
}