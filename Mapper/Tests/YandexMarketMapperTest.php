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
use BaksDev\Yandex\Market\Products\Mapper\YandexMarketMapper;
use BaksDev\Yandex\Market\Products\Repository\Card\CurrentYaMarketProductsCard\CurrentYaMarketProductCardInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group yandex-market-products
 */
#[When(env: 'test')]
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


        foreach($AllProductsIdentifier->findAllArray() as $key => $item)
        {
            if($key >= 10)
            {
                break;
            }

            $YaMarketCard = $YaMarketProductsCard
                ->forProduct($item['product_id'])
                ->forOfferConst($item['offer_const'])
                ->forVariationConst($item['variation_const'])
                ->forModificationConst($item['modification_const'])
                ->find();

            if($YaMarketCard === false)
            {
                continue;
            }

            if(empty($YaMarketCard['length']))
            {
                continue;
            }

            if(empty($YaMarketCard['width']))
            {
                continue;
            }

            if(empty($YaMarketCard['height']))
            {
                continue;
            }

            if(empty($YaMarketCard['weight']))
            {
                continue;
            }


            $request = $YandexMarketMapper->getData($YaMarketCard);

            self::assertEquals($request['offerId'], $YaMarketCard['article']);
            self::assertEquals(current($request['tags']), $YaMarketCard['product_card']);
            self::assertNotFalse(stripos($request['name'], $YaMarketCard['product_name']));
            self::assertEquals($request['marketCategoryId'], $YaMarketCard['market_category']);
            self::assertEquals($request['description'], $YaMarketCard['product_preview']);


            self::assertEquals($request['weightDimensions']['length'], $YaMarketCard['length'] / 10);
            self::assertEquals($request['weightDimensions']['width'], $YaMarketCard['width'] / 10);
            self::assertEquals($request['weightDimensions']['height'], $YaMarketCard['height'] / 10);
            self::assertEquals($request['weightDimensions']['weight'], $YaMarketCard['weight'] / 100);

            //dd($YaMarketCard);


            break;
        }

        self::assertTrue(true);


    }
}
