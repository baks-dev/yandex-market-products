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

namespace BaksDev\Yandex\Market\Products\Api\Products\Card\Find\Tests;

use BaksDev\Products\Product\Repository\AllProductsIdentifier\AllProductsIdentifierInterface;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Yandex\Market\Products\Api\Products\Card\Find\YaMarketProductFindCardRequest;
use BaksDev\Yandex\Market\Products\Repository\Card\CurrentYaMarketProductsCard\CurrentYaMarketProductCardInterface;
use BaksDev\Yandex\Market\Type\Authorization\YaMarketAuthorizationToken;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group yandex-market-products
 */
#[When(env: 'test')]
final class YandexMarketProductTest extends KernelTestCase
{
    private static YaMarketAuthorizationToken $Authorization;

    public static function setUpBeforeClass(): void
    {
        self::$Authorization = new YaMarketAuthorizationToken(
            profile: UserProfileUid::TEST,
            token: $_SERVER['TEST_YANDEX_MARKET_TOKEN'],
            company: (int) $_SERVER['TEST_YANDEX_MARKET_COMPANY'],
            business: (int) $_SERVER['TEST_YANDEX_MARKET_BUSINESS'],
            card: false,
            stocks: false,
        );
    }

    public function testUseCase(): void
    {
        /** @var AllProductsIdentifierInterface $AllProductsIdentifier */
        $AllProductsIdentifier = self::getContainer()->get(AllProductsIdentifierInterface::class);

        /** @var CurrentYaMarketProductCardInterface $YaMarketProductsCard */
        $YaMarketProductsCard = self::getContainer()->get(CurrentYaMarketProductCardInterface::class);

        /** @var YaMarketProductFindCardRequest $FindProductYandexMarketRequest */
        $FindProductYandexMarketRequest = self::getContainer()->get(YaMarketProductFindCardRequest::class);
        $FindProductYandexMarketRequest->TokenHttpClient(self::$Authorization);

        foreach($AllProductsIdentifier->findAll() as $i => $ProductsIdentifierResult)
        {
            if($i >= 100)
            {
                break;
            }

            $YaMarketCard = $YaMarketProductsCard
                ->forProduct($ProductsIdentifierResult->getProductId())
                ->forOfferConst($ProductsIdentifierResult->getProductOfferConst())
                ->forVariationConst($ProductsIdentifierResult->getProductVariationConst())
                ->forModificationConst($ProductsIdentifierResult->getProductModificationConst())
                ->forProfile(new UserProfileUid())
                ->find();

            if($YaMarketCard === false || empty($YaMarketCard['product_price']))
            {
                continue;
            }

            $articles = $FindProductYandexMarketRequest->article($YaMarketCard['article'])->find();
            self::assertIsBool($articles->valid());

            $tags = $FindProductYandexMarketRequest->forTag($YaMarketCard['product_card'])->find();
            self::assertIsBool($tags->valid());

            break;

        }

        self::assertFalse(false);

    }
}
