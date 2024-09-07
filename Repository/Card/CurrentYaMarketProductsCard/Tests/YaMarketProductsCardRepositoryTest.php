<?php
/*
 *  Copyright 2024.  Baks.dev <admin@baks.dev>
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

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Products\Product\Repository\AllProductsIdentifier\AllProductsIdentifierInterface;
use BaksDev\Yandex\Market\Products\Entity\Card\Market\YaMarketProductsCardMarket;
use BaksDev\Yandex\Market\Products\Repository\Card\CurrentYaMarketProductsCard\YaMarketProductsCardInterface;
use BaksDev\Yandex\Market\Products\Type\Card\Id\YaMarketProductsCardUid;
use Doctrine\ORM\EntityManagerInterface;
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

        foreach($AllProductsIdentifier->findAll() as $item)
        {
            $new = $YaMarketProductsCard
                ->forProduct($item['product_id'])
                ->forOfferConst($item['offer_const'])
                ->forVariationConst($item['variation_const'])
                ->forModificationConst($item['modification_const'])
                ->find();

            if($new === false)
            {
                continue;
            }

            self::assertTrue(array_key_exists("product_uid", $new));
            self::assertTrue(array_key_exists("product_card", $new));
            self::assertTrue(array_key_exists("offer_const", $new));
            self::assertTrue(array_key_exists("offer_const", $new));
            self::assertTrue(array_key_exists("product_offer_value", $new));
            self::assertTrue(array_key_exists("product_offer_postfix", $new));
            self::assertTrue(array_key_exists("variation_const", $new));
            self::assertTrue(array_key_exists("product_variation_value", $new));
            self::assertTrue(array_key_exists("product_variation_postfix", $new));
            self::assertTrue(array_key_exists("modification_const", $new));
            self::assertTrue(array_key_exists("product_modification_value", $new));
            self::assertTrue(array_key_exists("product_modification_postfix", $new));
            self::assertTrue(array_key_exists("product_name", $new));
            self::assertTrue(array_key_exists("product_preview", $new));
            self::assertTrue(array_key_exists("category_name", $new));
            self::assertTrue(array_key_exists("length", $new));
            self::assertTrue(array_key_exists("width", $new));
            self::assertTrue(array_key_exists("height", $new));
            self::assertTrue(array_key_exists("weight", $new));
            self::assertTrue(array_key_exists("market_category", $new));
            self::assertTrue(array_key_exists("product_propertys", $new));
            self::assertTrue(array_key_exists("product_params", $new));
            self::assertTrue(array_key_exists("product_images", $new));
            self::assertTrue(array_key_exists("product_price", $new));
            self::assertTrue(array_key_exists("product_currency", $new));
            self::assertTrue(array_key_exists("product_quantity", $new));
            self::assertTrue(array_key_exists("article", $new));

            break;
        }

        self::assertTrue(true);

    }
}
