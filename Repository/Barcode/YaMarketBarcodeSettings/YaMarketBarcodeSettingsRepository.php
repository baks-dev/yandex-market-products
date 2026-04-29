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
 *
 */

declare(strict_types=1);

namespace BaksDev\Yandex\Market\Products\Repository\Barcode\YaMarketBarcodeSettings;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Products\Product\Entity\Category\ProductCategory;
use BaksDev\Products\Product\Entity\Info\ProductInfo;
use BaksDev\Products\Product\Entity\Product;
use BaksDev\Products\Product\Entity\Property\ProductProperty;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Repository\UserProfileTokenStorage\UserProfileTokenStorageInterface;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Yandex\Market\Products\Entity\Barcode\Event\Counter\YaMarketBarcodeCounter;
use BaksDev\Yandex\Market\Products\Entity\Barcode\Event\Custom\YaMarketBarcodeCustom;
use BaksDev\Yandex\Market\Products\Entity\Barcode\Event\Name\YaMarketBarcodeName;
use BaksDev\Yandex\Market\Products\Entity\Barcode\Event\Offer\YaMarketBarcodeOffer;
use BaksDev\Yandex\Market\Products\Entity\Barcode\Event\Property\YaMarketBarcodeProperty;
use BaksDev\Yandex\Market\Products\Entity\Barcode\Event\Variation\YaMarketBarcodeVariation;
use BaksDev\Yandex\Market\Products\Entity\Barcode\YaMarketBarcode;
use InvalidArgumentException;

final class YaMarketBarcodeSettingsRepository implements YaMarketBarcodeSettingsInterface
{
    private ProductUid|false $product = false;

    private UserProfileUid|false $profile = false;

    public function __construct(
        private readonly DBALQueryBuilder $DBALQueryBuilder,
        private readonly UserProfileTokenStorageInterface $UserProfileTokenStorage
    ) {}

    public function forProduct(Product|ProductUid|string $product): self
    {
        if(empty($product))
        {
            $this->product = false;

            return $this;
        }

        if(is_string($product))
        {
            $product = new ProductUid($product);
        }

        if($product instanceof Product)
        {
            $product = $product->getId();
        }

        $this->product = $product;

        return $this;
    }

    public function forProfile(UserProfile|UserProfileUid|string $profile): self
    {
        if(empty($profile))
        {
            $this->profile = false;

            return $this;
        }

        if($profile instanceof UserProfile)
        {
            $profile = $profile->getId();
        }

        if(is_string($profile))
        {
            $profile = new UserProfileUid($profile);
        }

        $this->profile = $profile;

        return $this;
    }

    public function find(): YaMarketBarcodeSettingsResult|false
    {
        if(false === ($this->product instanceof ProductUid))
        {
            throw new InvalidArgumentException('Invalid Argument Product');
        }

        $dbal = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $dbal
            ->from(Product::class, 'product')
            ->where('product.id = :product')
            ->setParameter(
                key: 'product',
                value: $this->product,
                type: ProductUid::TYPE);


        $dbal->leftJoin(
            'product',
            ProductCategory::class,
            'product_category',
            '
                product_category.event = product.event AND 
                product_category.root = true',
        );

        $dbal->leftJoin(
            'product',
            ProductInfo::class,
            'product_info',
            'product_info.product = product.id',
        );

        $dbal
            ->join(
                'product_category',
                YaMarketBarcode::class,
                'barcode',
                '
                    barcode.id = product_category.category AND 
                    barcode.profile = :profile
            ')
            ->setParameter(
                key: 'profile',
                value: $this->profile ?: $this->UserProfileTokenStorage->getProfile(),
                type: UserProfileUid::TYPE,
            );

        $dbal
            ->addSelect('barcode_counter.value AS counter')
            ->leftJoin(
                'barcode',
                YaMarketBarcodeCounter::class,
                'barcode_counter',
                'barcode_counter.event = barcode.event',
            );

        $dbal
            ->addSelect('barcode_name.value AS name')
            ->leftJoin(
                'barcode',
                YaMarketBarcodeName::class,
                'barcode_name',
                'barcode_name.event = barcode.event',
            );

        $dbal
            ->addSelect('barcode_offer.value AS offer')
            ->leftJoin(
                'barcode',
                YaMarketBarcodeOffer::class,
                'barcode_offer',
                'barcode_offer.event = barcode.event',
            );

        $dbal
            ->addSelect('barcode_variation.value AS variation')
            ->leftJoin(
                'barcode',
                YaMarketBarcodeVariation::class,
                'barcode_variation',
                'barcode_variation.event = barcode.event',
            );

        $dbal
            ->addSelect('barcode_modification.value AS modification')
            ->leftJoin(
                'barcode',
                YaMarketBarcodeVariation::class,
                'barcode_modification',
                'barcode_modification.event = barcode.event',
            );

        /** Получаем настройки свойств */
        $dbal->leftJoin(
            'barcode',
            YaMarketBarcodeProperty::class,
            'ya_market_barcode_property',
            'ya_market_barcode_property.event = barcode.event',
        );

        $dbal->leftJoin(
            'ya_market_barcode_property',
            ProductProperty::class,
            'product_property',
            '
                product_property.event = product.event AND 
                product_property.field = ya_market_barcode_property.offer',
        );

        $dbal->addSelect("JSON_AGG ( DISTINCT
				
					JSONB_BUILD_OBJECT
					(
						'0', ya_market_barcode_property.sort,
						'name', ya_market_barcode_property.name,
						'value', product_property.value
					)
					
			) 
			 FILTER (WHERE product_property.value IS NOT NULL) 
            AS property
		");

        /** Получаем пользовательские свойства */
        $dbal->leftJoin(
            'barcode',
            YaMarketBarcodeCustom::class,
            'custom',
            'custom.event = barcode.event',
        );

        $dbal->addSelect("JSON_AGG ( DISTINCT
				
					JSONB_BUILD_OBJECT
					(
						'0', custom.sort,
						'name', custom.name,
						'value', custom.value
					)
					
			) 
			 FILTER (WHERE custom.value IS NOT NULL) 
            AS custom
		");

        $dbal->allGroupByExclude();

        return $dbal
            ->enableCache('yandex-market-products')
            ->fetchHydrate(YaMarketBarcodeSettingsResult::class);
    }
}