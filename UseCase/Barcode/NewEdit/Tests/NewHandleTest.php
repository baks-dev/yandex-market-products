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
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[Group('yandex-market-products')]
#[When(env: 'test')]
final class NewHandleTest extends KernelTestCase
{
    public static function setUpBeforeClass(): void
    {
        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);

        $YaMarketBarcode = $em->getRepository(YaMarketBarcode::class)
            ->findOneBy(['id' => CategoryProductUid::TEST, 'profile' => UserProfileUid::TEST]);

        if($YaMarketBarcode)
        {
            $em->remove($YaMarketBarcode);
        }

        $YaMarketBarcodeEventCollection = $em->getRepository(YaMarketBarcodeEvent::class)
            ->findBy(['main' => CategoryProductUid::TEST]);

        foreach($YaMarketBarcodeEventCollection as $remove)
        {
            $em->remove($remove);
        }

        $em->flush();
    }

    public function testUseCase(): void
    {
        $YaMarketBarcodeDTO = new YaMarketBarcodeDTO();

        // setCategory
        $CategoryProductUid = new CategoryProductUid();
        $YaMarketBarcodeDTO->setMain($CategoryProductUid);
        self::assertSame($CategoryProductUid, $YaMarketBarcodeDTO->getMain());

        $YaMarketBarcodeDTO->getName()->setValue(true);
        self::assertTrue($YaMarketBarcodeDTO->getName()->getValue());

        // setOffer
        $YaMarketBarcodeDTO->getOffer()->setValue(true);
        self::assertTrue($YaMarketBarcodeDTO->getOffer()->getValue());

        // setVariation
        $YaMarketBarcodeDTO->getVariation()->setValue(true);
        self::assertTrue($YaMarketBarcodeDTO->getVariation()->getValue());

        // setModification
        $YaMarketBarcodeDTO->getModification()->setValue(true);
        self::assertTrue($YaMarketBarcodeDTO->getModification()->getValue());

        // setCounter
        $YaMarketBarcodeDTO->getCounter()->setValue(3);
        self::assertEquals(3, $YaMarketBarcodeDTO->getCounter()->getValue());


        $YaMarketBarcodePropertyDTO = new YaMarketBarcodePropertyDTO();

        // setOffer
        $CategoryProductSectionFieldUid = new  CategoryProductSectionFieldUid();
        $YaMarketBarcodePropertyDTO->setOffer($CategoryProductSectionFieldUid);
        self::assertSame($CategoryProductSectionFieldUid, $YaMarketBarcodePropertyDTO->getOffer());

        // setSort
        $YaMarketBarcodePropertyDTO->setSort(100);
        self::assertEquals(100, $YaMarketBarcodePropertyDTO->getSort());

        // setName
        $YaMarketBarcodePropertyDTO->setName('Property');
        self::assertEquals('Property', $YaMarketBarcodePropertyDTO->getName());

        // addProperty
        $YaMarketBarcodeDTO->addProperty($YaMarketBarcodePropertyDTO);
        self::assertTrue($YaMarketBarcodeDTO->getProperty()->contains($YaMarketBarcodePropertyDTO));


        $YaMarketBarcodeCustomDTO = new YaMarketBarcodeCustomDTO();

        // setSort
        $YaMarketBarcodeCustomDTO->setSort(100);
        self::assertEquals(100, $YaMarketBarcodeCustomDTO->getSort());

        // setName
        $YaMarketBarcodeCustomDTO->setName('Custom');
        self::assertEquals('Custom', $YaMarketBarcodeCustomDTO->getName());

        // setValue
        $YaMarketBarcodeCustomDTO->setValue('Value');
        self::assertEquals('Value', $YaMarketBarcodeCustomDTO->getValue());

        // addCustom
        $YaMarketBarcodeDTO->addCustom($YaMarketBarcodeCustomDTO);
        self::assertTrue($YaMarketBarcodeDTO->getCustom()->contains($YaMarketBarcodeCustomDTO));


        self::bootKernel();

        /** @var YaMarketBarcodeHandler $YaMarketBarcodeHandler */
        $UserProfileUid = new UserProfileUid();
        $YaMarketBarcodeHandler = self::getContainer()->get(YaMarketBarcodeHandler::class);
        $handle = $YaMarketBarcodeHandler->handle($YaMarketBarcodeDTO, $UserProfileUid);

        self::assertTrue(($handle instanceof YaMarketBarcode), $handle.': Ошибка YaMarketBarcode');

    }

    public function testComplete(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);
        $YaMarketBarcode = $em->getRepository(YaMarketBarcode::class)
            ->findOneBy(['id' => CategoryProductUid::TEST, 'profile' => UserProfileUid::TEST]);
        self::assertNotNull($YaMarketBarcode);

        self::assertTrue(true);
    }
}