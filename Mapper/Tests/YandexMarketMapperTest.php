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

namespace BaksDev\Yandex\Market\Products\Mapper\Tests;

use BaksDev\Products\Product\Repository\AllProductsIdentifier\AllProductsIdentifierInterface;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Yandex\Market\Products\Mapper\YandexMarketMapper;
use BaksDev\Yandex\Market\Products\Repository\Card\CurrentYaMarketProductsCard\CurrentYaMarketProductCardInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
#[Group('yandex-market-products')]
class YandexMarketMapperTest extends KernelTestCase
{
    public function testUseCase(): void
    {
        /** @var AllProductsIdentifierInterface $AllProductsIdentifier */
        $AllProductsIdentifier = self::getContainer()->get(AllProductsIdentifierInterface::class);

        /** @var CurrentYaMarketProductCardInterface $YaMarketProductsCard */
        $YaMarketProductsCard = self::getContainer()->get(CurrentYaMarketProductCardInterface::class);

        /** @var YandexMarketMapper $YandexMarketMapper */
        $YandexMarketMapper = self::getContainer()->get(YandexMarketMapper::class);


        foreach($AllProductsIdentifier->findAll() as $key => $ProductsIdentifierResult)
        {
            if($key >= 10)
            {
                break;
            }

            $CurrentYaMarketProductCardResult = $YaMarketProductsCard
                ->forProduct($ProductsIdentifierResult->getProductId())
                ->forOfferConst($ProductsIdentifierResult->getProductOfferConst())
                ->forVariationConst($ProductsIdentifierResult->getProductVariationConst())
                ->forModificationConst($ProductsIdentifierResult->getProductModificationConst())
                ->forProfile(new UserProfileUid())
                ->find();

            if($CurrentYaMarketProductCardResult === false)
            {
                continue;
            }

            if(empty($CurrentYaMarketProductCardResult->getLength()))
            {
                continue;
            }

            if(empty($CurrentYaMarketProductCardResult->getWidth()))
            {
                continue;
            }

            if(empty($CurrentYaMarketProductCardResult->getHeight()))
            {
                continue;
            }

            if(empty($CurrentYaMarketProductCardResult->getWeight()))
            {
                continue;
            }

            $request = $YandexMarketMapper->getData($CurrentYaMarketProductCardResult);

            self::assertEquals($request['offerId'], $CurrentYaMarketProductCardResult->getArticle());
            self::assertEquals(current($request['tags']), $CurrentYaMarketProductCardResult->getGroupCard());
            self::assertNotFalse(stripos($request['name'], $CurrentYaMarketProductCardResult->getProductName()));
            self::assertEquals($request['marketCategoryId'], $CurrentYaMarketProductCardResult->getMarketCategory());
            self::assertEquals($request['description'], $CurrentYaMarketProductCardResult->getProductPreview());


            self::assertEquals($request['weightDimensions']['length'], $CurrentYaMarketProductCardResult->getLength());
            self::assertEquals($request['weightDimensions']['width'], $CurrentYaMarketProductCardResult->getWidth());
            self::assertEquals($request['weightDimensions']['height'], $CurrentYaMarketProductCardResult->getHeight());
            self::assertEquals($request['weightDimensions']['weight'], $CurrentYaMarketProductCardResult->getWeight());

            //dd($CurrentYaMarketProductCardResult);


            break;
        }

        self::assertTrue(true);


    }
}
