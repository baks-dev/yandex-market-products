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
use BaksDev\Yandex\Market\Products\Repository\Card\CurrentYaMarketProductsCard\YaMarketProductsCardInterface;
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
        /** @var YaMarketProductsCardInterface $YaMarketProductsCard */
        $YaMarketProductsCard = self::getContainer()->get(YaMarketProductsCardInterface::class);

        /** @var AllProductsIdentifierInterface $AllProductsIdentifier */
        $AllProductsIdentifier = self::getContainer()->get(AllProductsIdentifierInterface::class);

        foreach($AllProductsIdentifier->findAll() as $i => $item)
        {
            if($i >= 100)
            {
                break;
            }

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
                "product_propertys",
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
