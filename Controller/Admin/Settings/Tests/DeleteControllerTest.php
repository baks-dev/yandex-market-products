<?php
/*
 *  Copyright 2022.  Baks.dev <admin@baks.dev>
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *   limitations under the License.
 *
 */

namespace BaksDev\Yandex\Market\Products\Controller\Admin\Settings\Tests;

use BaksDev\Users\User\Tests\TestUserAccount;
use BaksDev\Yandex\Market\Products\Entity\Settings\YaMarketProductsSettings;
use BaksDev\Yandex\Market\Products\Type\Settings\Event\YaMarketProductsSettingsEventUid;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;
use BaksDev\Yandex\Market\Products\UseCase\Settings\NewEdit\Tests\WbProductSettingsNewTest;

/**
 * @group yandex-market-products
 * @group yandex-market-products-settings
 *
 * @depends BaksDev\Yandex\Market\Products\UseCase\Settings\NewEdit\Tests\WbProductSettingsNewTest::class
 */
#[When(env: 'test')]
final class DeleteControllerTest extends WebTestCase
{
    private const URL = '/admin/wb/product/setting/delete/%s';

    private const ROLE = 'ROLE_YA_MARKET_PRODUCTS_SETTING_DELETE';


    public function testRoleSuccessful(): void
    {

        //self::ensureKernelShutdown();
        $client = static::createClient();

        foreach(TestUserAccount::getDevice() as $device)
        {
            $client->setServerParameter('HTTP_USER_AGENT', $device);

            $usr = TestUserAccount::getModer(self::ROLE);

            $client->loginUser($usr, 'user');
            $client->request('GET', sprintf(self::URL, YaMarketProductsSettingsEventUid::TEST));

            self::assertResponseIsSuccessful();
        }

        self::assertTrue(true);

    }

    // доступ по роли ROLE_ADMIN
    public function testRoleAdminSuccessful(): void
    {

        //self::ensureKernelShutdown();
        $client = static::createClient();

        foreach(TestUserAccount::getDevice() as $device)
        {
            $client->setServerParameter('HTTP_USER_AGENT', $device);

            $usr = TestUserAccount::getAdmin();

            $client->loginUser($usr, 'user');
            $client->request('GET', sprintf(self::URL, YaMarketProductsSettingsEventUid::TEST));

            self::assertResponseIsSuccessful();
        }

        self::assertTrue(true);

    }

    // доступ по роли ROLE_USER
    public function testRoleUserDeny(): void
    {

        //self::ensureKernelShutdown();
        $client = static::createClient();

        foreach(TestUserAccount::getDevice() as $device)
        {
            $client->setServerParameter('HTTP_USER_AGENT', $device);

            $usr = TestUserAccount::getUsr();
            $client->loginUser($usr, 'user');
            $client->request('GET', sprintf(self::URL, YaMarketProductsSettingsEventUid::TEST));

            self::assertResponseStatusCodeSame(403);
        }

        self::assertTrue(true);
    }

    /** Доступ по без роли */
    public function testGuestFiled(): void
    {

        //self::ensureKernelShutdown();
        $client = static::createClient();

        foreach(TestUserAccount::getDevice() as $device)
        {
            $client->setServerParameter('HTTP_USER_AGENT', $device);

            $client->request('GET', sprintf(self::URL, YaMarketProductsSettingsEventUid::TEST));

            // Full authentication is required to access this resource
            self::assertResponseStatusCodeSame(401);
        }

        self::assertTrue(true);
    }
//
//    public function testComplete(): void
//    {
//        self::assertTrue(true);
//    }
}
