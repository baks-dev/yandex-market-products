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

namespace BaksDev\Yandex\Market\Products\UseCase\Settings\Delete\Tests;

use BaksDev\Core\Doctrine\ORMQueryBuilder;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Category\Type\Section\Field\Id\CategoryProductSectionFieldUid;
use BaksDev\Yandex\Market\Products\Controller\Admin\Settings\Tests\DeleteControllerTest;
use BaksDev\Yandex\Market\Products\Entity\Barcode\Event\WbBarcodeEvent;
use BaksDev\Yandex\Market\Products\Entity\Settings\Event\YaMarketProductsSettingsEvent;
use BaksDev\Yandex\Market\Products\Entity\Settings\YaMarketProductsSettings;
use BaksDev\Yandex\Market\Products\UseCase\Settings\Delete\DeleteYaMarketProductsSettingsDTO;
use BaksDev\Yandex\Market\Products\UseCase\Settings\Delete\DeleteYaMarketProductsSettingsHandler;
use BaksDev\Yandex\Market\Products\UseCase\Settings\NewEdit\Tests\WbProductSettingsEditTest;
use BaksDev\Yandex\Market\Products\UseCase\Settings\NewEdit\YaMarketProductsSettingsDTO;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group yandex-market-products
 * @group yandex-market-products-settings
 *
 * @depends BaksDev\Yandex\Market\Products\Controller\Admin\Settings\Tests\DeleteControllerTest::class
 * @depends BaksDev\Yandex\Market\Products\UseCase\Settings\NewEdit\Tests\WbProductSettingsEditTest::class
 *
 * @see WbProductSettingsEditTest
 * @see DeleteControllerTest
 *
 */
#[When(env: 'test')]
final class YaMarketProductsSettingsDeleteTest extends KernelTestCase
{

    public function testUseCase(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        /** @var ORMQueryBuilder $ORMQueryBuilder */
        $ORMQueryBuilder = $container->get(ORMQueryBuilder::class);
        $qb = $ORMQueryBuilder->createQueryBuilder(self::class);
        $CategoryProductUid = new CategoryProductUid();

        $qb
            ->from(YaMarketProductsSettings::class, 'main')
            ->where('main.id = :category')
            ->setParameter('category', $CategoryProductUid, CategoryProductUid::TYPE);

        $qb
            ->select('event')
            ->leftJoin(YaMarketProductsSettingsEvent::class,
                'event',
                'WITH',
                'event.id = main.event'
            );


        /** @var WbBarcodeEvent $WbProductSettingsEvent */
        $WbProductSettingsEvent = $qb->getQuery()->getOneOrNullResult();



        /**  WbBarcodeDTO  */


        $WbProductCardDTO = new YaMarketProductsSettingsDTO();
        $WbProductSettingsEvent->getDto($WbProductCardDTO);

        /**
         * WbProductCardDTO
         */

        self::assertEquals(CategoryProductUid::TEST, (string) $WbProductCardDTO->getSettings());
        self::assertEquals('GCRIVEHZUY', $WbProductCardDTO->getName());

        /**
         * WbProductSettingsPropertyDTO
         */

        $WbProductSettingsPropertyDTO = $WbProductCardDTO->getProperty()->current();

        self::assertEquals('fTXTGyZxIr', $WbProductSettingsPropertyDTO->getType());
        self::assertEquals(CategoryProductSectionFieldUid::TEST, (string) $WbProductSettingsPropertyDTO->getField());


        /** DELETE */

        $DeleteWbProductSettingsDTO = new DeleteYaMarketProductsSettingsDTO();
        $WbProductSettingsEvent->getDto($DeleteWbProductSettingsDTO);

        /** @var DeleteYaMarketProductsSettingsHandler $DeleteWbProductSettingsHandler */
        $DeleteWbProductSettingsHandler = $container->get(DeleteYaMarketProductsSettingsHandler::class);
        $handle = $DeleteWbProductSettingsHandler->handle($DeleteWbProductSettingsDTO);
        self::assertTrue(($handle instanceof YaMarketProductsSettings), $handle.': Ошибка WbProductSettings');

    }


    /**
     * @depends testUseCase
     */
    public function testComplete(): void
    {
        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);


        /** WbProductSettings */

        $WbProductSettings = $em->getRepository(YaMarketProductsSettings::class)
            ->find(CategoryProductUid::TEST);

        if($WbProductSettings)
        {
            $em->remove($WbProductSettings);
        }


        /** WbProductSettingsEvent */

        $WbProductSettingsEventCollection = $em->getRepository(YaMarketProductsSettingsEvent::class)
            ->findBy(['settings' => CategoryProductUid::TEST]);

        foreach($WbProductSettingsEventCollection as $remove)
        {
            $em->remove($remove);
        }

        $em->flush();

        self::assertNull($WbProductSettings);

        $em->clear();
        //$em->close();

    }

}
