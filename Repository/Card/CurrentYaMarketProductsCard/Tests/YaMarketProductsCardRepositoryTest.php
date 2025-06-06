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
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
use BaksDev\Yandex\Market\Products\Repository\Card\CurrentYaMarketProductsCard\CurrentYaMarketProductCardInterface;
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

        foreach($AllProductsIdentifier->findAllArray() as $i => $item)
        {
            if($i >= 100)
            {
                //break;
            }

            $item['product_id'] = new ProductUid('01878a9e-26ff-7f71-bb70-6cb19c044cd6');
            $item['offer_const'] = new ProductOfferConst('01878a9e-25db-76e0-9ed8-2b4155abda46');
            $item['variation_const'] = new ProductVariationConst('01878a9e-25df-7cc3-9fca-8e9e3e61d099');
            $item['modification_const'] = new ProductModificationConst('01878a9e-25e1-7a55-92e1-11d4210d077c');


            /** Если не указана настройка соотношений - карточки не найдет */
            $current = $YaMarketProductsCard
                ->forProduct($item['product_id'])
                ->forOfferConst($item['offer_const'])
                ->forVariationConst($item['variation_const'])
                ->forModificationConst($item['modification_const'])
                ->find();


            if($current === false)
            {
                continue;
            }


            $array_keys = [
                "product_uid",
                "product_card",
                "offer_const",
                "product_offer_value",
                "product_offer_postfix",
                "variation_const",
                "product_variation_value",
                "product_variation_postfix",
                "modification_const",
                "product_modification_value",
                "product_modification_postfix",
                "product_name",
                "product_preview",
                "category_name",
                "length",
                "width",
                "height",
                "weight",
                "market_category",
                "product_properties",
                "product_params",
                "product_images",
                "product_price",
                "product_old_price",
                "product_currency",
                "product_quantity",
                "article",
                "barcode"
            ];

            foreach($current as $key => $value)
            {
                self::assertTrue(in_array($key, $array_keys), sprintf('Появился новый ключ %s', $key));
            }

            foreach($array_keys as $key)
            {
                self::assertTrue(array_key_exists($key, $current));
            }

            break;
        }

        self::assertTrue(true);

    }
}
