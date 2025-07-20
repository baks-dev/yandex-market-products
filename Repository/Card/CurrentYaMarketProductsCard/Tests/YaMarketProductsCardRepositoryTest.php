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

namespace BaksDev\Yandex\Market\Products\Repository\Card\CurrentYaMarketProductsCard\Tests;

use BaksDev\Products\Product\Repository\AllProductsIdentifier\AllProductsIdentifierInterface;
use BaksDev\Products\Product\Repository\AllProductsIdentifier\ProductsIdentifierResult;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Yandex\Market\Products\Repository\Card\CurrentYaMarketProductsCard\CurrentYaMarketProductCardInterface;
use BaksDev\Yandex\Market\Products\Repository\Card\CurrentYaMarketProductsCard\CurrentYaMarketProductCardResult;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group yandex-market-products
 */
#[When(env: 'test')]
class YaMarketProductsCardRepositoryTest extends KernelTestCase
{
    public function testUseCase(): void
    {
        /** @var CurrentYaMarketProductCardInterface $YaMarketProductsCard */
        $YaMarketProductsCard = self::getContainer()->get(CurrentYaMarketProductCardInterface::class);

        /** @var AllProductsIdentifierInterface $AllProductsIdentifier */
        $AllProductsIdentifier = self::getContainer()->get(AllProductsIdentifierInterface::class);

        foreach($AllProductsIdentifier->findAll() as $i => $ProductsIdentifierResult)
        {
            if($i >= 100)
            {
                break;
            }

            /*$ProductsIdentifierResult = new ProductsIdentifierResult(
                '01878a9e-26ff-7f71-bb70-6cb19c044cd6',
                '01878a9e-26ff-7f71-bb70-6cb19c044cd6',
                'e573362f-7d5e-75d3-94d2-788a364fdd60',
                '01878a9e-25db-76e0-9ed8-2b4155abda46',
                'e573362f-7d5e-75d3-94d2-788a364fdd60',
                '01878a9e-25df-7cc3-9fca-8e9e3e61d099',
                'e573362f-7d5e-75d3-94d2-788a364fdd60',
                '01878a9e-25e1-7a55-92e1-11d4210d077c',
            );*/

            /** Если не указана настройка соотношений - карточки не найдет */
            $CurrentYaMarketProductCardResult = $YaMarketProductsCard
                ->forProduct($ProductsIdentifierResult->getProductId())
                ->forOfferConst($ProductsIdentifierResult->getProductOfferConst())
                ->forVariationConst($ProductsIdentifierResult->getProductVariationConst())
                ->forModificationConst($ProductsIdentifierResult->getProductModificationConst())
                ->forProfile(new UserProfileUid())
                ->find();

            if(false === ($CurrentYaMarketProductCardResult instanceof CurrentYaMarketProductCardResult))
            {
                continue;
            }

            self::assertIsBool($CurrentYaMarketProductCardResult->isCredentials()); //: bool
            self::assertInstanceOf(ProductUid::class, $CurrentYaMarketProductCardResult->getProductId()); //: ProductUid
            $CurrentYaMarketProductCardResult->getGroupCard(); //: false|string
            $CurrentYaMarketProductCardResult->getOfferConst(); //: ProductOfferConst|false
            $CurrentYaMarketProductCardResult->getProductOfferValue(); //: false|string
            $CurrentYaMarketProductCardResult->getProductOfferPostfix(); //: false|string
            $CurrentYaMarketProductCardResult->getVariationConst(); //: ProductVariationConst|false
            $CurrentYaMarketProductCardResult->getProductVariationValue(); //: false|string
            $CurrentYaMarketProductCardResult->getProductVariationPostfix(); //: false|string
            $CurrentYaMarketProductCardResult->getModificationConst(); //: ProductModificationConst|false
            $CurrentYaMarketProductCardResult->getProductModificationValue(); //: false|string
            $CurrentYaMarketProductCardResult->getProductModificationPostfix(); //:; // false|string
            $CurrentYaMarketProductCardResult->getProductName(); //: string
            $CurrentYaMarketProductCardResult->getProductPreview(); //: null|string
            $CurrentYaMarketProductCardResult->getCategoryName(); //: string
            $CurrentYaMarketProductCardResult->getLength(); //: false|float
            $CurrentYaMarketProductCardResult->getWidth(); //: false|float
            $CurrentYaMarketProductCardResult->getHeight(); //: false|float
            $CurrentYaMarketProductCardResult->getWeight(); //: false|float
            $CurrentYaMarketProductCardResult->getMarketCategory(); //: int
            $CurrentYaMarketProductCardResult->getProductProperties(); //: array|false
            $CurrentYaMarketProductCardResult->getProductParams(); //: array|false
            $CurrentYaMarketProductCardResult->getProductImages(); //: array|false
            $CurrentYaMarketProductCardResult->getProductPrice(); //: Money|false
            $CurrentYaMarketProductCardResult->getProductOldPrice(); //: Money|false
            $CurrentYaMarketProductCardResult->getProductCurrency(); //: Currency
            $CurrentYaMarketProductCardResult->getProductQuantity(); //: int
            $CurrentYaMarketProductCardResult->getArticle(); //: false|string
            $CurrentYaMarketProductCardResult->getBarcode(); //: false|string

            break;
        }

        self::assertTrue(true);

    }
}
