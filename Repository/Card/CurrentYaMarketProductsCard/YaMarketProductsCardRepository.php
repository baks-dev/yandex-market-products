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

namespace BaksDev\Yandex\Market\Products\Repository\Card\CurrentYaMarketProductsCard;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\DeliveryTransport\Entity\ProductParameter\DeliveryPackageProductParameter;
use BaksDev\Products\Category\Entity\CategoryProduct;
use BaksDev\Products\Category\Entity\Section\Field\CategoryProductSectionField;
use BaksDev\Products\Category\Entity\Trans\CategoryProductTrans;
use BaksDev\Products\Product\Entity\Category\ProductCategory;
use BaksDev\Products\Product\Entity\Description\ProductDescription;
use BaksDev\Products\Product\Entity\Event\ProductEvent;
use BaksDev\Products\Product\Entity\Info\ProductInfo;
use BaksDev\Products\Product\Entity\Offers\Image\ProductOfferImage;
use BaksDev\Products\Product\Entity\Offers\Price\ProductOfferPrice;
use BaksDev\Products\Product\Entity\Offers\ProductOffer;
use BaksDev\Products\Product\Entity\Offers\Quantity\ProductOfferQuantity;
use BaksDev\Products\Product\Entity\Offers\Variation\Image\ProductVariationImage;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\Image\ProductModificationImage;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\Price\ProductModificationPrice;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\ProductModification;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\Quantity\ProductModificationQuantity;
use BaksDev\Products\Product\Entity\Offers\Variation\Price\ProductVariationPrice;
use BaksDev\Products\Product\Entity\Offers\Variation\ProductVariation;
use BaksDev\Products\Product\Entity\Offers\Variation\Quantity\ProductVariationQuantity;
use BaksDev\Products\Product\Entity\Photo\ProductPhoto;
use BaksDev\Products\Product\Entity\Price\ProductPrice;
use BaksDev\Products\Product\Entity\Product;
use BaksDev\Products\Product\Entity\Property\ProductProperty;
use BaksDev\Products\Product\Entity\Trans\ProductTrans;
use BaksDev\Yandex\Market\Products\Entity\Card\Event\YaMarketProductsCardEvent;
use BaksDev\Yandex\Market\Products\Entity\Card\Market\YaMarketProductsCardMarket;
use BaksDev\Yandex\Market\Products\Entity\Card\YaMarketProductsCard;
use BaksDev\Yandex\Market\Products\Entity\Settings\Event\YaMarketProductsSettingsEvent;
use BaksDev\Yandex\Market\Products\Entity\Settings\Parameters\YaMarketProductsSettingsParameters;
use BaksDev\Yandex\Market\Products\Entity\Settings\Property\YaMarketProductsSettingsProperty;
use BaksDev\Yandex\Market\Products\Entity\Settings\YaMarketProductsSettings;
use BaksDev\Yandex\Market\Products\Type\Card\Id\YaMarketProductsCardUid;


final class YaMarketProductsCardRepository implements YaMarketProductsCardInterface
{
    private DBALQueryBuilder $DBALQueryBuilder;

    public function __construct(
        DBALQueryBuilder $DBALQueryBuilder,
    )
    {
        $this->DBALQueryBuilder = $DBALQueryBuilder;
    }


    /**
     * Метод получает активную карточку по идентификатору
     */
    public function findByCard(YaMarketProductsCardUid|string $card): array|bool
    {
        if(is_string($card))
        {
            $card = new YaMarketProductsCardUid($card);
        }

        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $dbal
            ->from(YaMarketProductsCard::class, 'card')
            ->where('card.id = :card')
            ->setParameter('card', $card, YaMarketProductsCardUid::TYPE);

        $dbal
            ->addSelect('card_market.profile')
            ->addSelect('card_market.sku AS article')
            ->addSelect('card_market.product AS product_uid')
            ->addSelect('card_market.offer AS offer_const')
            ->addSelect('card_market.variation AS variation_const')
            ->addSelect('card_market.modification AS modification_const')
            ->leftJoin(
                'card',
                YaMarketProductsCardMarket::class,
                'card_market',
                'card_market.main = card.id'
            );


        $dbal
            ->join(
                'card_market',
                Product::class,
                'product',
                'product.id = card_market.product'
            );


        /* ProductInfo */

        $dbal
            ->addSelect('product_info.article AS product_card')
            ->leftJoin(
                'product',
                ProductInfo::class,
                'product_info',
                'product_info.product = product.id'
            );

        $dbal
            ->addSelect('product_offer.value AS product_offer_value')
            ->addSelect('product_offer.postfix AS product_offer_postfix')
            ->join(
                'product',
                ProductOffer::class,
                'product_offer',
                '
                    product_offer.event = product.event AND 
                    product_offer.const = card_market.offer
            ');

        $dbal
            ->addSelect('product_variation.value AS product_variation_value')
            ->addSelect('product_variation.postfix AS product_variation_postfix')
            ->join(
                'product_offer',
                ProductVariation::class,
                'product_variation',
                '
                product_variation.offer = product_offer.id AND 
                product_variation.const = card_market.variation
            ');

        $dbal
            ->addSelect('product_modification.value AS product_modification_value')
            ->addSelect('product_modification.postfix AS product_modification_postfix')
            ->join(
                'product_variation',
                ProductModification::class,
                'product_modification',
                '
                product_modification.variation = product_variation.id AND 
                product_modification.const = card_market.modification
            ');


        $dbal
            ->addSelect('product_trans.name AS product_name')
            ->leftJoin(
                'product',
                ProductTrans::class,
                'product_trans',
                'product_trans.event = product.event'
            );


        $dbal
            ->addSelect('product_desc.preview AS product_preview')
            ->leftJoin(
                'product',
                ProductDescription::class,
                'product_desc',
                'product_desc.event = product.event AND product_desc.device = :device '

            )->setParameter('device', 'pc');


        /* Категория */
        $dbal->leftJoin(
            'product',
            ProductCategory::class,
            'product_category',
            'product_category.event = product.event AND product_category.root = true'
        );


        $dbal->join(
            'product_category',
            CategoryProduct::class,
            'category',
            'category.id = product_category.category'
        );

        $dbal
            ->addSelect('category_trans.name AS category_name')
            ->leftJoin(
                'category',
                CategoryProductTrans::class,
                'category_trans',
                'category_trans.event = category.event AND category_trans.local = :local'
            );


        $dbal
            ->addSelect('product_package.length') // Длина упаковки в см.
            ->addSelect('product_package.width') // Ширина упаковки в см.
            ->addSelect('product_package.height') // Высота упаковки в см.
            ->addSelect('product_package.weight') // Вес товара в кг с учетом упаковки (брутто).
            ->leftJoin(
                'card_market',
                DeliveryPackageProductParameter::class,
                'product_package',
                '
                    product_package.product = card_market.product AND
                    product_package.offer = card_market.offer AND
                    product_package.variation = card_market.variation AND
                    product_package.modification = card_market.modification
                '
            );


        /**
         * Категория, согласно настройкам соотношений
         */


        $dbal
            ->join(
                'product_category',
                YaMarketProductsSettings::class,
                'settings',
                'settings.id = product_category.category'
            );


        $dbal
            ->addSelect('settings_event.market AS market_category')
            ->leftJoin(
                'settings',
                YaMarketProductsSettingsEvent::class,
                'settings_event',
                'settings_event.id = settings.event'
            );


        /**
         * Свойства по умолчанию
         */


        $dbal
            ->leftJoin(
                'settings',
                YaMarketProductsSettingsProperty::class,
                'settings_property',
                'settings_property.event = settings.event'
            );

        // Получаем значение из свойств товара
        $dbal
            ->leftJoin(
                'settings_property',
                ProductProperty::class,
                'product_property',
                'product_property.event = product.event AND product_property.field = settings_property.field'
            );


        $dbal->addSelect("JSON_AGG
			( DISTINCT

					JSONB_BUILD_OBJECT
					(
						'type', settings_property.type,
			
						'value', CASE
						   WHEN product_property.value IS NOT NULL THEN product_property.value
						   WHEN settings_property.def IS NOT NULL THEN settings_property.def
						   ELSE NULL
						END
						
				
					)

			)
			AS product_propertys"
        );


        /**
         * Параметры
         */


        $dbal
            ->leftJoin(
                'settings',
                YaMarketProductsSettingsParameters::class,
                'settings_params',
                'settings_params.event = settings.event'
            );


        // Получаем значение из свойств товара
        $dbal
            ->leftJoin(
                'settings_params',
                ProductProperty::class,
                'product_property_params',
                '
                product_property_params.event = product.event AND 
                product_property_params.field = settings_params.field
            ');

        // Получаем значение из модификации множественного варианта

        $dbal
            ->leftJoin(
                'settings_params',
                ProductOffer::class,
                'product_offer_params',
                '
                    product_offer_params.id = product_offer.id AND  
                    product_offer_params.category_offer = settings_params.field
            ');


        $dbal
            ->leftJoin(
                'settings_params',
                ProductVariation::class,
                'product_variation_params',
                '
                    product_variation_params.id = product_variation.id AND 
                    product_variation_params.category_variation = settings_params.field
           ');


        $dbal
            ->leftJoin(
                'settings_params',
                ProductModification::class,
                'product_modification_params',
                '
                    product_modification_params.id = product_modification.id AND 
                    product_modification_params.category_modification = settings_params.field
            ');


        $dbal->addSelect("JSON_AGG
			( DISTINCT

					JSONB_BUILD_OBJECT
					(
						'name', settings_params.type,
						
						'value', CASE
						   WHEN product_property_params.value IS NOT NULL THEN product_property_params.value
						   WHEN product_modification_params.value IS NOT NULL THEN product_modification_params.value
						   WHEN product_variation_params.value IS NOT NULL THEN product_variation_params.value
						   WHEN product_offer_params.value IS NOT NULL THEN product_offer_params.value
						   ELSE NULL
						END 
					)

			)
			AS product_params"
        );


        /**
         * Фото продукции
         */

        /* Фото модификаций */

        $dbal->leftJoin(
            'product_modification',
            ProductModificationImage::class,
            'product_modification_image',
            'product_modification_image.modification = product_modification.id'
        );


        /* Фото вариантов */

        $dbal->leftJoin(
            'product_offer',
            ProductVariationImage::class,
            'product_variation_image',
            '
			product_variation_image.variation = product_variation.id
			'
        );


        /* Фот торговых предложений */

        $dbal->leftJoin(
            'product_offer',
            ProductOfferImage::class,
            'product_offer_images',
            '
			
			product_offer_images.offer = product_offer.id
			
		'
        );

        /* Фото продукта */

        $dbal->leftJoin(
            'product',
            ProductPhoto::class,
            'product_photo',
            '
	
			product_photo.event = product.event
			'
        );

        $dbal->addSelect(
            "JSON_AGG
		( DISTINCT
				CASE 
				
				WHEN product_offer_images.ext IS NOT NULL 
				THEN JSONB_BUILD_OBJECT
					(
						'product_img_root', product_offer_images.root,
						'product_img', CONCAT ( '/upload/".$dbal->table(ProductOfferImage::class)."' , '/', product_offer_images.name),
						'product_img_ext', product_offer_images.ext,
						'product_img_cdn', product_offer_images.cdn
						

					) 
					
				WHEN product_variation_image.ext IS NOT NULL 
				THEN JSONB_BUILD_OBJECT
					(
						'product_img_root', product_variation_image.root,
						'product_img', CONCAT ( '/upload/".$dbal->table(ProductVariationImage::class)."' , '/', product_variation_image.name),
						'product_img_ext', product_variation_image.ext,
						'product_img_cdn', product_variation_image.cdn
					)	
					
					
				WHEN product_modification_image.ext IS NOT NULL 
				THEN JSONB_BUILD_OBJECT
					(
						'product_img_root', product_modification_image.root,
						'product_img', CONCAT ( '/upload/".$dbal->table(ProductModificationImage::class)."' , '/', product_modification_image.name),
						'product_img_ext', product_modification_image.ext,
						'product_img_cdn', product_modification_image.cdn
						

					)
					
				WHEN product_photo.ext IS NOT NULL 
				THEN JSONB_BUILD_OBJECT
					(
						'product_img_root', product_photo.root,
						'product_img', CONCAT ( '/upload/".$dbal->table(ProductPhoto::class)."' , '/', product_photo.name),
						'product_img_ext', product_photo.ext,
						'product_img_cdn', product_photo.cdn
						
					)
					
			
				END

				 
			) AS product_images
	    ");


        /* Базовая Цена товара */
        $dbal->leftJoin(
            'product',
            ProductPrice::class,
            'product_price',
            'product_price.event = product.event'
        );

        /* Цена торгового предо жения */
        $dbal->leftJoin(
            'product_offer',
            ProductOfferPrice::class,
            'product_offer_price',
            'product_offer_price.offer = product_offer.id'
        );

        /* Цена множественного варианта */
        $dbal->leftJoin(
            'product_variation',
            ProductVariationPrice::class,
            'product_variation_price',
            'product_variation_price.variation = product_variation.id'
        );


        /* Цена модификации множественного варианта */
        $dbal->leftJoin(
            'product_modification',
            ProductModificationPrice::class,
            'product_modification_price',
            'product_modification_price.modification = product_modification.id'
        );

        /* Стоимость продукта */

        $dbal->addSelect(
            '
			CASE
			   WHEN product_modification_price.price IS NOT NULL AND product_modification_price.price > 0 THEN product_modification_price.price
			   WHEN product_variation_price.price IS NOT NULL AND product_variation_price.price > 0 THEN product_variation_price.price
			   WHEN product_offer_price.price IS NOT NULL AND product_offer_price.price > 0 THEN product_offer_price.price
			   WHEN product_price.price IS NOT NULL AND product_price.price > 0 THEN product_price.price
			   ELSE NULL
			END AS product_price
		');

        /* Валюта продукта */

        $dbal->addSelect(
            '
			CASE
			   WHEN product_modification_price.price IS NOT NULL AND product_modification_price.price > 0 THEN product_modification_price.currency
			   WHEN product_variation_price.price IS NOT NULL AND product_variation_price.price > 0 THEN product_variation_price.currency
			   WHEN product_offer_price.price IS NOT NULL AND product_offer_price.price > 0 THEN product_offer_price.currency
			   WHEN product_price.price IS NOT NULL AND product_price.price > 0 THEN product_price.currency
			   ELSE NULL
			END AS product_currency
		'
        );


        /* Наличие и резерв торгового предложения */
        $dbal->leftJoin(
            'product_offer',
            ProductOfferQuantity::class,
            'product_offer_quantity',
            'product_offer_quantity.offer = product_offer.id'
        );

        /* Наличие и резерв множественного варианта */
        $dbal->leftJoin(
            'product_variation',
            ProductVariationQuantity::class,
            'product_variation_quantity',
            'product_variation_quantity.variation = product_variation.id'
        );

        /* Наличие и резерв модификации множественного варианта */
        $dbal->leftJoin(
            'product_modification',
            ProductModificationQuantity::class,
            'product_modification_quantity',
            'product_modification_quantity.modification = product_modification.id'
        );


        /* Наличие продукта */

        $dbal->addSelect(
            '

            CASE
			   WHEN product_modification_quantity.quantity > 0 AND product_modification_quantity.quantity > product_modification_quantity.reserve 
			   THEN (product_modification_quantity.quantity - ABS(product_modification_quantity.reserve))
			
			   WHEN product_variation_quantity.quantity > 0 AND product_variation_quantity.quantity > product_variation_quantity.reserve 
			   THEN (product_variation_quantity.quantity - ABS(product_variation_quantity.reserve))
			
			   WHEN product_offer_quantity.quantity > 0 AND product_offer_quantity.quantity > product_offer_quantity.reserve 
			   THEN (product_offer_quantity.quantity - ABS(product_offer_quantity.reserve))
			  
			   WHEN product_price.quantity > 0 AND product_price.quantity > product_price.reserve 
			   THEN (product_price.quantity - ABS(product_price.reserve))
			 
			   ELSE 0
			   
			END AS product_quantity
            
		')
            ->addGroupBy('product_modification_quantity.reserve')
            ->addGroupBy('product_variation_quantity.reserve')
            ->addGroupBy('product_offer_quantity.reserve')
            ->addGroupBy('product_price.reserve');

        $dbal->allGroupByExclude();

        /** Кешируем в кеш модуля products-product для сброса при обновлении карточки */
        return $dbal
            ->enableCache('products-product', 60)
            ->fetchAssociative();
    }
}
