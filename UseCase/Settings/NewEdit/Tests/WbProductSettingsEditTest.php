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
use BaksDev\Yandex\Market\Products\Entity\Settings\Event\YaMarketProductsSettingsEvent;
use BaksDev\Yandex\Market\Products\Entity\Settings\YaMarketProductsSettings;
use BaksDev\Yandex\Market\Products\Type\Settings\Event\YaMarketProductsSettingsEventUid;
use BaksDev\Yandex\Market\Products\UseCase\Settings\NewEdit\Property\YaMarketProductsSettingsPropertyDTO;
use BaksDev\Yandex\Market\Products\UseCase\Settings\NewEdit\YaMarketProductsSettingsDTO;
use BaksDev\Yandex\Market\Products\UseCase\Settings\NewEdit\YaMarketProductsSettingsHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;
use BaksDev\Yandex\Market\Products\Controller\Admin\Settings\Tests\EditControllerTest;

/**
 * @group yandex-market-products
 * @group yandex-market-products-settings
 *
 * @depends BaksDev\Yandex\Market\Products\Controller\Admin\Settings\Tests\EditControllerTest::class
 *
 * @see EditControllerTest
 */
#[When(env: 'test')]
final class WbProductSettingsEditTest extends KernelTestCase
{

    public function testUseCase(): void
    {
        //self::bootKernel();
        $container = self::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        $WbProductSettingsEvent = $em->getRepository(YaMarketProductsSettingsEvent::class)->find(YaMarketProductsSettingsEventUid::TEST);
        self::assertNotNull($WbProductSettingsEvent);


        $WbProductsSettingsDTO = new YaMarketProductsSettingsDTO();
        $WbProductSettingsEvent->getDto($WbProductsSettingsDTO);


        self::assertEquals(CategoryProductUid::TEST, (string) $WbProductsSettingsDTO->getSettings());

        self::assertEquals('ffxZnGTCbd', $WbProductsSettingsDTO->getName());
        $WbProductsSettingsDTO->setName('GCRIVEHZUY');


        /** @var YaMarketProductsSettingsPropertyDTO $WbProductSettingsPropertyDTO */

        $WbProductSettingsPropertyDTO = $WbProductsSettingsDTO->getProperty()->current();

        self::assertEquals('MsUPpqkQHD', $WbProductSettingsPropertyDTO->getType());
        $WbProductSettingsPropertyDTO->setType('fTXTGyZxIr');

        self::assertEquals(CategoryProductSectionFieldUid::TEST, (string) $WbProductSettingsPropertyDTO->getField());
        $WbProductSettingsPropertyDTO->setField(new CategoryProductSectionFieldUid());



        /** Вспомогательные свойства */

        $WbProductSettingsPropertyDTO->setUnit('rRSzqbqKxA');
        self::assertEquals('rRSzqbqKxA', $WbProductSettingsPropertyDTO->getUnit());

        $WbProductSettingsPropertyDTO->setPopular(false);
        self::assertFalse($WbProductSettingsPropertyDTO->isPopular());

        $WbProductSettingsPropertyDTO->setRequired(false);
        self::assertFalse($WbProductSettingsPropertyDTO->isRequired());


        /** UPDATE */

        self::bootKernel();

        /** @var YaMarketProductsSettingsHandler $WbProductsSettingsHandler */
        $WbProductsSettingsHandler = self::getContainer()->get(YaMarketProductsSettingsHandler::class);
        $handle = $WbProductsSettingsHandler->handle($WbProductsSettingsDTO);
        self::assertTrue(($handle instanceof YaMarketProductsSettings), $handle.': Ошибка WbProductSettings');

        $em->clear();
        //$em->close();

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