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

namespace BaksDev\Yandex\Market\Products\UseCase\Barcode\NewEdit\Tests;


use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Category\Type\Section\Field\Id\CategoryProductSectionFieldUid;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Yandex\Market\Products\Entity\Barcode\Event\YaMarketBarcodeEvent;
use BaksDev\Yandex\Market\Products\Entity\Barcode\YaMarketBarcode;
use BaksDev\Yandex\Market\Products\UseCase\Barcode\NewEdit\Custom\YaMarketBarcodeCustomDTO;
use BaksDev\Yandex\Market\Products\UseCase\Barcode\NewEdit\Property\YaMarketBarcodePropertyDTO;
use BaksDev\Yandex\Market\Products\UseCase\Barcode\NewEdit\YaMarketBarcodeDTO;
use BaksDev\Yandex\Market\Products\UseCase\Barcode\NewEdit\YaMarketBarcodeHandler;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[Group('yandex-market-products')]
#[When(env: 'test')]
final class EditHandleTest extends KernelTestCase
{

    #[DependsOnClass(NewHandleTest::class)]
    public function testUseCase(): void
    {

        self::bootKernel();
        $container = self::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        /** @var YaMarketBarcodeEvent $YaMarketBarcodeEvent */

        $YaMarketBarcodeEvent =
            $em->createQueryBuilder()
                ->select('event')
                ->from(YaMarketBarcode::class, 'barcode')
                ->where('barcode.id = :id')
                ->setParameter('id', CategoryProductUid::TEST, CategoryProductUid::TYPE)
                ->leftJoin(YaMarketBarcodeEvent::class, 'event', 'WITH', 'event.id = barcode.event')
                ->getQuery()
                ->getOneOrNullResult();


        self::assertNotNull($YaMarketBarcodeEvent);


        $YaMarketBarcodeDTO = new YaMarketBarcodeDTO();
        $YaMarketBarcodeEvent->getDto($YaMarketBarcodeDTO);

        self::assertTrue($YaMarketBarcodeDTO->getName()->getValue());
        $YaMarketBarcodeDTO->getName()->setValue(false);

        self::assertTrue($YaMarketBarcodeDTO->getOffer()->getValue());
        $YaMarketBarcodeDTO->getOffer()->setValue(false);

        self::assertTrue($YaMarketBarcodeDTO->getVariation()->getValue());
        $YaMarketBarcodeDTO->getVariation()->setValue(false);

        self::assertTrue($YaMarketBarcodeDTO->getModification()->getValue());
        $YaMarketBarcodeDTO->getModification()->setValue(false);

        self::assertEquals(3, $YaMarketBarcodeDTO->getCounter()->getValue());
        $YaMarketBarcodeDTO->getCounter()->setValue(5);

        /** @var YaMarketBarcodePropertyDTO $YaMarketBarcodePropertyDTO */
        $YaMarketBarcodePropertyDTO = $YaMarketBarcodeDTO->getProperty()->current();

        self::assertEquals(CategoryProductSectionFieldUid::TEST, (string) $YaMarketBarcodePropertyDTO->getOffer());
        self::assertEquals(100, $YaMarketBarcodePropertyDTO->getSort());
        self::assertEquals('Property', $YaMarketBarcodePropertyDTO->getName());


        /** @var YaMarketBarcodeCustomDTO $YaMarketBarcodeCustomDTO */
        $YaMarketBarcodeCustomDTO = $YaMarketBarcodeDTO->getCustom()->current();

        self::assertEquals(100, $YaMarketBarcodeCustomDTO->getSort());
        self::assertEquals('Custom', $YaMarketBarcodeCustomDTO->getName());
        self::assertEquals('Value', $YaMarketBarcodeCustomDTO->getValue());


        /** EDIT */


        // Property
        $YaMarketBarcodePropertyDTO->setOffer(new  CategoryProductSectionFieldUid());
        $YaMarketBarcodePropertyDTO->setSort(50);
        $YaMarketBarcodePropertyDTO->setName('Property Edit');

        // Custom
        $YaMarketBarcodeCustomDTO->setSort(50);
        $YaMarketBarcodeCustomDTO->setName('Custom Edit');
        $YaMarketBarcodeCustomDTO->setValue('Value Edit');


        $UserProfileUid = new UserProfileUid();
        /** @var YaMarketBarcodeHandler $YaMarketBarcodeHandler */
        $YaMarketBarcodeHandler = $container->get(YaMarketBarcodeHandler::class);
        $handle = $YaMarketBarcodeHandler->handle($YaMarketBarcodeDTO, $UserProfileUid);

        self::assertTrue(($handle instanceof YaMarketBarcode), $handle.': Ошибка YaMarketBarcode');
    }
}