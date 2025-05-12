<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Yandex\Market\Products\Repository\AllProductsWithYandexMarketImage\Tests;

use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Invariable\ProductInvariableUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationUid;
use BaksDev\Yandex\Market\Products\Forms\YandexMarketFilter\YandexMarketProductsFilterDTO;
use BaksDev\Yandex\Market\Products\Repository\AllProductsWithYandexMarketImage\AllProductsWithYandexMarketImagesInterface;
use BaksDev\Yandex\Market\Products\Repository\AllProductsWithYandexMarketImage\AllProductsWithYandexMarketImagesResult;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group yandex-market-products
 * @group yandex-market-products-repository
 */
#[When(env: 'test')]
final class AllProductsWithYandexMarketImagesTest extends KernelTestCase
{
    public function testFindAll(): void
    {
        /** @var AllProductsWithYandexMarketImagesInterface $allProductsWithYandexMarketImages */
        $allProductsWithYandexMarketImages = self::getContainer()
            ->get(AllProductsWithYandexMarketImagesInterface::class);

        $yandexMarketFilter = new YandexMarketProductsFilterDTO();
        $result = $allProductsWithYandexMarketImages
            ->filterYandexMarketProducts($yandexMarketFilter)
            ->findAll()
            ->getData();

        /** @var AllProductsWithYandexMarketImagesResult $item */
        foreach($result as $item)
        {
            self::assertInstanceOf(ProductUid::class, $item->getId());
            self::assertInstanceOf(ProductEventUid::class, $item->getEvent());
            self::assertInstanceOf(ProductInvariableUid::class, $item->getInvariable());
            self::assertIsString($item->getProductName());
            self::assertTrue(
                $item->getProductOfferId() === null ||
                $item->getProductOfferId() instanceof ProductOfferUid
            );
            self::assertTrue(
                $item->getProductOfferValue() === null ||
                is_string($item->getProductOfferValue())
            );
            self::assertTrue(
                $item->getProductOfferConst() === null ||
                $item->getProductOfferConst() instanceof ProductOfferConst
            );
            self::assertTrue(
                $item->getProductOfferPostfix() === null ||
                is_string($item->getProductOfferPostfix())
            );
            self::assertTrue(
                $item->getProductOfferReference() === null ||
                is_string($item->getProductOfferReference())
            );
            self::assertTrue(
                $item->getProductVariationId() === null ||
                $item->getProductVariationId() instanceof ProductVariationUid
            );
            self::assertTrue(
                $item->getProductVariationValue() === null ||
                is_string($item->getProductVariationValue())
            );
            self::assertTrue(
                $item->getProductVariationConst() === null ||
                $item->getProductVariationConst() instanceof ProductVariationConst
            );
            self::assertTrue(
                $item->getProductVariationPostfix() === null ||
                is_string($item->getProductVariationPostfix())
            );
            self::assertTrue(
                $item->getProductVariationReference() === null ||
                is_string($item->getProductVariationReference())
            );
            self::assertTrue(
                $item->getProductModificationId() === null ||
                $item->getProductModificationId() instanceof ProductModificationUid
            );
            self::assertTrue(
                $item->getProductModificationValue() === null ||
                is_string($item->getProductModificationValue())
            );
            self::assertTrue(
                $item->getProductModificationConst() === null ||
                $item->getProductModificationConst() instanceof ProductModificationConst
            );
            self::assertTrue(
                $item->getProductModificationPostfix() === null ||
                is_string($item->getProductModificationPostfix())
            );
            self::assertTrue(
                $item->getProductModificationReference() === null ||
                is_string($item->getProductModificationReference())
            );

            break;
        }
    }
}