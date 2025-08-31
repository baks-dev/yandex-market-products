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
use PHPUnit\Framework\Attributes\Group;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
#[Group('yandex-market-products')]
class YaMarketProductsCardRepositoryTest extends KernelTestCase
{
    public function testUseCase(): void
    {

        /** @var CurrentYaMarketProductCardInterface $YaMarketProductsCard */
        $YaMarketProductsCard = self::getContainer()->get(CurrentYaMarketProductCardInterface::class);

        // Предполагается, что эти UUID соответствуют данным, загруженным вашими тестовыми фикстурами.
        $productUid = new ProductUid($_SERVER['TEST_PRODUCT']);
        $offerConst = new ProductOfferConst($_SERVER['TEST_OFFER_CONST']);
        $variationConst = new ProductVariationConst($_SERVER['TEST_VARIATION_CONST']);
        $modificationConst = new ProductModificationConst($_SERVER['TEST_MODIFICATION_CONST']);


        /** Если не указана настройка соотношений - карточки не найдет */
        $CurrentYaMarketProductCardResult = $YaMarketProductsCard
            ->forProduct($productUid)
            ->forOfferConst($offerConst)
            ->forVariationConst($variationConst)
            ->forModificationConst($modificationConst)
            //->forProfile(new UserProfileUid('019577a9-71a3-714b-a99c-0386833d802f'))
            ->find();


        if($CurrentYaMarketProductCardResult instanceof CurrentYaMarketProductCardResult)
        {
            // Вызываем все геттеры
            $reflectionClass = new ReflectionClass(CurrentYaMarketProductCardResult::class);
            $methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);

            foreach($methods as $method)
            {
                // Методы без аргументов
                if($method->getNumberOfParameters() === 0)
                {
                    // Вызываем метод
                    $method->invoke($CurrentYaMarketProductCardResult);
                }
            }
        }

        self::assertTrue(true);

    }
}
