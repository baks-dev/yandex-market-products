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

namespace BaksDev\Yandex\Market\Products\UseCase\Barcode\Delete\Tests;

use BaksDev\Core\Doctrine\ORMQueryBuilder;
use BaksDev\Yandex\Market\Products\Entity\Barcode\Event\YaMarketBarcodeEvent;
use BaksDev\Yandex\Market\Products\Entity\Barcode\YaMarketBarcode;
use BaksDev\Yandex\Market\Products\UseCase\Barcode\Delete\YaMarketBarcodeDeleteDTO;
use BaksDev\Yandex\Market\Products\UseCase\Barcode\Delete\YaMarketBarcodeDeleteHandler;
use BaksDev\Yandex\Market\Products\UseCase\Barcode\NewEdit\Custom\YaMarketBarcodeCustomDTO;
use BaksDev\Yandex\Market\Products\UseCase\Barcode\NewEdit\YaMarketBarcodeDTO;
use BaksDev\Yandex\Market\Products\UseCase\Barcode\NewEdit\Property\YaMarketBarcodePropertyDTO;
use BaksDev\Yandex\Market\Products\UseCase\Barcode\NewEdit\Tests\EditHandleTest;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Category\Type\Section\Field\Id\CategoryProductSectionFieldUid;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[Group('yandex-market-products')]
#[When(env: 'test')]
final class YaMarketBarcodeDeleteTest extends KernelTestCase
{

    #[DependsOnClass(EditHandleTest::class)]
    public function testUseCase(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        /** @var ORMQueryBuilder $ORMQueryBuilder */
        $ORMQueryBuilder = $container->get(ORMQueryBuilder::class);
        $qb = $ORMQueryBuilder->createQueryBuilder(self::class);

        $qb
            ->from(YaMarketBarcode::class, 'barcode')
            ->where('barcode.id = :category')
            ->setParameter('category', CategoryProductUid::TEST, CategoryProductUid::TYPE);

        $qb
            ->select('event')
            ->leftJoin(YaMarketBarcodeEvent::class,
                'event',
                'WITH',
                'event.id = barcode.event',
            );


        /** @var YaMarketBarcodeEvent $YaMarketBarcodeEvent */
        $YaMarketBarcodeEvent = $qb->getQuery()->getOneOrNullResult();

        $YaMarketBarcodeDTO = new YaMarketBarcodeDTO();
        $YaMarketBarcodeEvent->getDto($YaMarketBarcodeDTO);


        self::assertFalse($YaMarketBarcodeDTO->getName()->getValue());
        self::assertFalse($YaMarketBarcodeDTO->getOffer()->getValue());
        self::assertFalse($YaMarketBarcodeDTO->getVariation()->getValue());
        self::assertFalse($YaMarketBarcodeDTO->getModification()->getValue());
        self::assertEquals(5, $YaMarketBarcodeDTO->getCounter()->getValue());


        /** @var YaMarketBarcodePropertyDTO $YaMarketBarcodePropertyDTO */
        $YaMarketBarcodePropertyDTO = $YaMarketBarcodeDTO->getProperty()->current();

        self::assertEquals(CategoryProductSectionFieldUid::TEST, (string) $YaMarketBarcodePropertyDTO->getOffer());
        self::assertEquals(50, $YaMarketBarcodePropertyDTO->getSort());
        self::assertEquals('Property Edit', $YaMarketBarcodePropertyDTO->getName());


        /** @var YaMarketBarcodeCustomDTO $YaMarketBarcodeCustomDTO */
        $YaMarketBarcodeCustomDTO = $YaMarketBarcodeDTO->getCustom()->current();

        self::assertEquals(50, $YaMarketBarcodeCustomDTO->getSort());
        self::assertEquals('Custom Edit', $YaMarketBarcodeCustomDTO->getName());
        self::assertEquals('Value Edit', $YaMarketBarcodeCustomDTO->getValue());


        /** DELETE */

        $YaMarketBarcodeDeleteDTO = new YaMarketBarcodeDeleteDTO();
        $YaMarketBarcodeEvent->getDto($YaMarketBarcodeDeleteDTO);

        /** @var YaMarketBarcodeDeleteHandler $YaMarketBarcodeDeleteHandler */
        $UserProfileUid = new UserProfileUid();
        $YaMarketBarcodeDeleteHandler = $container->get(YaMarketBarcodeDeleteHandler::class);
        $handle = $YaMarketBarcodeDeleteHandler->handle($YaMarketBarcodeDeleteDTO, $UserProfileUid);
        self::assertTrue(($handle instanceof YaMarketBarcode), $handle.': Ошибка YaMarketBarcode');


        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        $YaMarketBarcode = $em->getRepository(YaMarketBarcode::class)
            ->findOneBy(['id' => CategoryProductUid::TEST, 'profile' => UserProfileUid::TEST]);
        self::assertNull($YaMarketBarcode);

    }

    #[Depends('testUseCase')]
    public function testComplete(): void
    {
        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);

        /* YaMarketBarcode */

        $YaMarketBarcode = $em->getRepository(YaMarketBarcode::class)
            ->findOneBy(['id' => CategoryProductUid::TEST, 'profile' => UserProfileUid::TEST]);

        if($YaMarketBarcode)
        {
            $em->remove($YaMarketBarcode);
        }

        /* YaMarketBarcodeEvent */

        $YaMarketBarcodeEventCollection = $em->getRepository(YaMarketBarcodeEvent::class)
            ->findBy(['main' => CategoryProductUid::TEST]);

        foreach($YaMarketBarcodeEventCollection as $remove)
        {
            $em->remove($remove);
        }

        $em->flush();

        self::assertNull($YaMarketBarcode);
    }
}
