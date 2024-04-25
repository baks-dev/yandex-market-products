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

namespace BaksDev\Yandex\Market\Products\UseCase\Settings\NewEdit\Tests;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Category\Type\Section\Field\Id\CategoryProductSectionFieldUid;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\UsersTable\Entity\Actions\Event\UsersTableActionsEvent;
use BaksDev\Users\UsersTable\Entity\Actions\UsersTableActions;
use BaksDev\Users\UsersTable\Type\Actions\Id\UsersTableActionsUid;
use BaksDev\Yandex\Market\Products\Entity\Barcode\WbBarcode;
use BaksDev\Yandex\Market\Products\Entity\Settings\Event\YaMarketProductsSettingsEvent;
use BaksDev\Yandex\Market\Products\Entity\Settings\YaMarketProductsSettings;
use BaksDev\Yandex\Market\Products\UseCase\Settings\NewEdit\Property\YaMarketProductsSettingsPropertyDTO;
use BaksDev\Yandex\Market\Products\UseCase\Settings\NewEdit\YaMarketProductsSettingsDTO;
use BaksDev\Yandex\Market\Products\UseCase\Settings\NewEdit\YaMarketProductsSettingsHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group yandex-market-products
 * @group yandex-market-products-settings
 */
#[When(env: 'test')]
final class WbProductSettingsNewTest extends KernelTestCase
{

    public static function setUpBeforeClass(): void
    {
        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);

        $WbProductSettings = $em->getRepository(YaMarketProductsSettings::class)
            ->find(CategoryProductUid::TEST);


        if($WbProductSettings)
        {
            $WbProductSettingsEventCollection = $em->getRepository(YaMarketProductsSettingsEvent::class)
                ->findBy(['settings' => CategoryProductUid::TEST]);

            foreach($WbProductSettingsEventCollection as $remove)
            {
                $em->remove($remove);
            }

            $em->remove($WbProductSettings);

            $em->flush();
        }

        $em->clear();
        //$em->close();
    }


    public function testUseCase(): void
    {
        /**
         * WbProductCardDTO
         */

        $WbProductCardDTO = new YaMarketProductsSettingsDTO();

        $CategoryProductUid = new CategoryProductUid();
        $WbProductCardDTO->setSettings($CategoryProductUid);
        self::assertSame($CategoryProductUid, $WbProductCardDTO->getSettings());

        $WbProductCardDTO->setName('ffxZnGTCbd');
        self::assertEquals('ffxZnGTCbd', $WbProductCardDTO->getName());


        /**
         * WbProductSettingsPropertyDTO
         */

        $WbProductSettingsPropertyDTO = new YaMarketProductsSettingsPropertyDTO();
        $WbProductCardDTO->addProperty($WbProductSettingsPropertyDTO);
        self::assertTrue($WbProductCardDTO->getProperty()->contains($WbProductSettingsPropertyDTO));

        $WbProductSettingsPropertyDTO->setType('MsUPpqkQHD');
        self::assertEquals('MsUPpqkQHD', $WbProductSettingsPropertyDTO->getType());

        $WbProductSettingsPropertyDTO->setUnit('QgrHGKVVrd');
        self::assertEquals('QgrHGKVVrd', $WbProductSettingsPropertyDTO->getUnit());


        $WbProductSettingsPropertyDTO->setPopular(true);
        self::assertTrue($WbProductSettingsPropertyDTO->isPopular());


        $WbProductSettingsPropertyDTO->setRequired(true);
        self::assertTrue($WbProductSettingsPropertyDTO->isRequired());

        $field = new CategoryProductSectionFieldUid();
        $WbProductSettingsPropertyDTO->setField($field);
        self::assertSame($field, $WbProductSettingsPropertyDTO->getField());


        /** PERSIST */

        self::bootKernel();

        /** @var YaMarketProductsSettingsHandler $WbProductsSettingsHandler */
        $WbProductsSettingsHandler = self::getContainer()->get(YaMarketProductsSettingsHandler::class);
        $handle = $WbProductsSettingsHandler->handle($WbProductCardDTO);

        self::assertTrue(($handle instanceof YaMarketProductsSettings), $handle.': Ошибка WbProductSettings');

    }


    public function testComplete(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        /** @var DBALQueryBuilder $dbal */
        $dbal = $container->get(DBALQueryBuilder::class);

        $dbal->createQueryBuilder(self::class);
        $dbal
            ->from(YaMarketProductsSettings::class, 'test')
            ->where('test.id = :id')
            ->setParameter('id', CategoryProductUid::TEST)
        ;

        self::assertTrue($dbal->fetchExist());
    }
}