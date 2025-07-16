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

namespace BaksDev\Yandex\Market\Products\Repository\Card\CurrentYaMarketProductsCard;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\DeliveryTransport\Entity\ProductParameter\DeliveryPackageProductParameter;
use BaksDev\Products\Category\Entity\CategoryProduct;
use BaksDev\Products\Category\Entity\Trans\CategoryProductTrans;
use BaksDev\Products\Product\Entity\Category\ProductCategory;
use BaksDev\Products\Product\Entity\Description\ProductDescription;
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
use BaksDev\Products\Product\Entity\ProductInvariable;
use BaksDev\Products\Product\Entity\Property\ProductProperty;
use BaksDev\Products\Product\Entity\Trans\ProductTrans;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
use BaksDev\Products\Stocks\BaksDevProductsStocksBundle;
use BaksDev\Products\Stocks\Entity\Total\ProductStockTotal;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Info\UserProfileInfo;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Yandex\Market\Products\Entity\Custom\Images\YandexMarketProductCustomImage;
use BaksDev\Yandex\Market\Products\Entity\Custom\YandexMarketProductCustom;
use BaksDev\Yandex\Market\Products\Entity\Settings\Event\YaMarketProductsSettingsEvent;
use BaksDev\Yandex\Market\Products\Entity\Settings\Parameters\YaMarketProductsSettingsParameters;
use BaksDev\Yandex\Market\Products\Entity\Settings\Property\YaMarketProductsSettingsProperty;
use BaksDev\Yandex\Market\Products\Entity\Settings\YaMarketProductsSettings;
use InvalidArgumentException;

final class CurrentYaMarketProductCardRepository implements CurrentYaMarketProductCardInterface
{

    private UserProfileUid|false $profile = false;

    public function __construct(private readonly DBALQueryBuilder $DBALQueryBuilder) {}

    /**
     * ID продукта
     */
    private ProductUid|null $product = null;

    /**
     * Постоянный уникальный идентификатор ТП
     */
    private ProductOfferConst|null|false $offerConst = null;

    /**
     * Постоянный уникальный идентификатор варианта
     */
    private ProductVariationConst|null|false $variationConst = null;

    /**
     * Постоянный уникальный идентификатор модификации
     */
    private ProductModificationConst|null|false $modificationConst = null;


    public function forProduct(Product|ProductUid|string $product): self
    {
        if(empty($product))
        {
            $this->product = null;
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

    public function forOfferConst(ProductOfferConst|string|null|false $offerConst): self
    {
        if(empty($offerConst))
        {
            $offerConst = false;
        }

        if(is_string($offerConst))
        {
            $offerConst = new ProductOfferConst($offerConst);
        }

        $this->offerConst = $offerConst;

        return $this;
    }

    public function forVariationConst(ProductVariationConst|string|null|false $variationConst): self
    {
        if(empty($variationConst))
        {
            $variationConst = false;
        }

        if(is_string($variationConst))
        {
            $variationConst = new ProductVariationConst($variationConst);
        }

        $this->variationConst = $variationConst;

        return $this;
    }

    public function forModificationConst(ProductModificationConst|string|null|false $modificationConst): self
    {
        if(empty($modificationConst))
        {
            $modificationConst = false;
        }

        if(is_string($modificationConst))
        {
            $modificationConst = new ProductModificationConst($modificationConst);
        }

        $this->modificationConst = $modificationConst;

        return $this;
    }

    public function forProfile(UserProfileUid $profile): self
    {
        $this->profile = $profile;

        return $this;
    }

    /**
     * Метод получает активную карточку по идентификатору
     */
    public function find(): CurrentYaMarketProductCardResult|false
    {
        if(is_null($this->product))
        {
            throw new InvalidArgumentException('Invalid Argument product');
        }

        if(is_null($this->offerConst))
        {
            throw new InvalidArgumentException('Invalid Argument offerConst');
        }

        if(is_null($this->variationConst))
        {
            throw new InvalidArgumentException('Invalid Argument variationConst');
        }

        if(is_null($this->modificationConst))
        {
            throw new InvalidArgumentException('Invalid Argument modificationConst');
        }


        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        /*
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
        */

        $dbal
            ->addSelect('product.id AS product_uid')
            ->from(Product::class, 'product')
            ->where('product.id = :product')
            ->setParameter('product', $this->product, ProductUid::TYPE);


        /* ProductInfo */

        $dbal
            ->addSelect('product_info.article AS product_card')
            ->leftJoin(
                'product',
                ProductInfo::class,
                'product_info',
                'product_info.product = product.id',
            );


        $dbal
            ->addSelect('product_offer.const AS offer_const')
            ->addSelect('product_offer.value AS product_offer_value')
            ->addSelect('product_offer.postfix AS product_offer_postfix');

        if($this->offerConst instanceof ProductOfferConst)
        {
            $dbal
                ->join(
                    'product',
                    ProductOffer::class,
                    'product_offer',
                    '
                    product_offer.event = product.event AND 
                    product_offer.const = :offer_const
            ',
                )->setParameter(
                    'offer_const',
                    $this->offerConst,
                    ProductOfferConst::TYPE,
                );
        }
        else
        {
            $dbal->join(
                'product',
                ProductOffer::class,
                'product_offer',
                'product_offer.event = product.event AND 
                    product_offer.const IS NULL',
            );
        }


        $dbal
            ->addSelect('product_variation.const AS variation_const')
            ->addSelect('product_variation.value AS product_variation_value')
            ->addSelect('product_variation.postfix AS product_variation_postfix');

        if($this->variationConst instanceof ProductVariationConst)
        {
            $dbal
                ->join(
                    'product_offer',
                    ProductVariation::class,
                    'product_variation',
                    '
                product_variation.offer = product_offer.id AND 
                product_variation.const = :variation_const
            ',
                )->setParameter(
                    'variation_const',
                    $this->variationConst,
                    ProductVariationConst::TYPE,
                );

        }
        else
        {
            $dbal
                ->leftJoin(
                    'product_offer',
                    ProductVariation::class,
                    'product_variation',
                    '
                product_variation.offer = product_offer.id AND 
                product_variation.const IS NULL
            ',
                );
        }


        $dbal
            ->addSelect('product_modification.const AS modification_const')
            ->addSelect('product_modification.value AS product_modification_value')
            ->addSelect('product_modification.postfix AS product_modification_postfix');

        if($this->modificationConst instanceof ProductModificationConst)
        {
            $dbal
                ->join(
                    'product_variation',
                    ProductModification::class,
                    'product_modification',
                    '
                product_modification.variation = product_variation.id AND 
                product_modification.const = :modification_const
            ',
                )->setParameter(
                    'modification_const',
                    $this->modificationConst,
                    ProductModificationConst::TYPE,
                );
        }
        else
        {
            $dbal
                ->leftJoin(
                    'product_variation',
                    ProductModification::class,
                    'product_modification',
                    'product_modification.variation = product_variation.id AND 
                        product_modification.const IS NULL',
                );
        }


        $dbal
            ->addSelect('product_trans.name AS product_name')
            ->leftJoin(
                'product',
                ProductTrans::class,
                'product_trans',
                'product_trans.event = product.event',
            );


        $dbal
            ->addSelect('product_desc.preview AS product_preview')
            ->leftJoin(
                'product',
                ProductDescription::class,
                'product_desc',
                'product_desc.event = product.event AND product_desc.device = :device ',
            )->setParameter('device', 'pc');


        /* Категория */
        $dbal->leftJoin(
            'product',
            ProductCategory::class,
            'product_category',
            'product_category.event = product.event AND product_category.root = true',
        );


        $dbal->join(
            'product_category',
            CategoryProduct::class,
            'category',
            'category.id = product_category.category',
        );

        $dbal
            ->addSelect('category_trans.name AS category_name')
            ->leftJoin(
                'category',
                CategoryProductTrans::class,
                'category_trans',
                'category_trans.event = category.event AND category_trans.local = :local',
            );

        $dbal
            ->addSelect('product_package.length') // Длина упаковки в см.
            ->addSelect('product_package.width') // Ширина упаковки в см.
            ->addSelect('product_package.height') // Высота упаковки в см.
            ->addSelect('product_package.weight') // Вес товара в кг с учетом упаковки (брутто).
            ->leftJoin(
                'product_variation',
                DeliveryPackageProductParameter::class,
                'product_package',
                'product_package.product = product.id  AND 
                    
                    (
                        (product_offer.const IS NOT NULL AND product_package.offer = product_offer.const) OR 
                        (product_offer.const IS NULL AND product_package.offer IS NULL)
                    )
                    
                    AND
                     
                    (
                        (product_variation.const IS NOT NULL AND product_package.variation = product_variation.const) OR 
                        (product_variation.const IS NULL AND product_package.variation IS NULL)
                    )
                     
                   AND
                   
                   (
                        (product_modification.const IS NOT NULL AND product_package.modification = product_modification.const) OR 
                        (product_modification.const IS NULL AND product_package.modification IS NULL)
                   )
                ',
            );


        /**
         * Категория, согласно настройкам соотношений
         */


        $dbal
            ->join(
                'product_category',
                YaMarketProductsSettings::class,
                'settings',
                'settings.id = product_category.category',
            );


        $dbal
            ->addSelect('settings_event.market AS market_category')
            ->leftJoin(
                'settings',
                YaMarketProductsSettingsEvent::class,
                'settings_event',
                'settings_event.id = settings.event',
            );


        /**
         * Свойства по умолчанию
         */


        $dbal
            ->leftJoin(
                'settings',
                YaMarketProductsSettingsProperty::class,
                'settings_property',
                'settings_property.event = settings.event',
            );

        // Получаем значение из свойств товара
        $dbal
            ->leftJoin(
                'settings_property',
                ProductProperty::class,
                'product_property',
                'product_property.event = product.event AND product_property.field = settings_property.field',
            );

        $dbal->addSelect(
            "JSON_AGG
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
			AS product_properties",
        );


        /**
         * Параметры
         */


        $dbal
            ->leftJoin(
                'settings',
                YaMarketProductsSettingsParameters::class,
                'settings_params',
                'settings_params.event = settings.event',
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
            ',
            );


        // Получаем значение из модификации множественного варианта

        $dbal
            ->leftJoin(
                'settings_params',
                ProductOffer::class,
                'product_offer_params',
                '
                    product_offer_params.id = product_offer.id AND  
                    product_offer_params.category_offer = settings_params.field
            ',
            );


        $dbal
            ->leftJoin(
                'settings_params',
                ProductVariation::class,
                'product_variation_params',
                '
                    product_variation_params.id = product_variation.id AND 
                    product_variation_params.category_variation = settings_params.field
           ',
            );


        $dbal
            ->leftJoin(
                'settings_params',
                ProductModification::class,
                'product_modification_params',
                '
                    product_modification_params.id = product_modification.id AND 
                    product_modification_params.category_modification = settings_params.field
            ',
            );


        $dbal->addSelect(
            "JSON_AGG
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
			AS product_params",
        );

        /**
         * Product Invariable
         */
        $dbal->leftJoin(
            'product_modification',
            ProductInvariable::class,
            'product_invariable',
            '
                   product_invariable.product = product.id AND
                   (
                       (product_offer.const IS NOT NULL AND product_invariable.offer = product_offer.const) OR
                       (product_offer.const IS NULL AND product_invariable.offer IS NULL)
                   )
                   AND
                   (
                       (product_variation.const IS NOT NULL AND product_invariable.variation = product_variation.const) OR
                       (product_variation.const IS NULL AND product_invariable.variation IS NULL)
                   )
                  AND
                  (
                       (product_modification.const IS NOT NULL AND product_invariable.modification = product_modification.const) OR
                       (product_modification.const IS NULL AND product_invariable.modification IS NULL)
                  )
           ');


        /** Кастомные настройки продукта Яндекс Маркет  */

        $dbal
            ->leftJoin(
                'product_invariable',
                YandexMarketProductCustom::class,
                'ya_market_product',
                'ya_market_product.invariable = product_invariable.id',
            );


        /**
         * Фото продукции
         */

        /* Кастомные фото Яндекс Маркет */
        $dbal->leftJoin(
            'ya_market_product',
            YandexMarketProductCustomImage::class,
            'ya_market_product_images',
            '
                ya_market_product_images.invariable = ya_market_product.invariable
            ',
        );

        /* Фото модификаций */
        $dbal->leftJoin(
            'product_modification',
            ProductModificationImage::class,
            'product_modification_image',
            'product_modification_image.modification = product_modification.id',
        );


        /* Фото вариантов */
        $dbal->leftJoin(
            'product_offer',
            ProductVariationImage::class,
            'product_variation_image',
            '
			product_variation_image.variation = product_variation.id
			',
        );


        /* Фот торговых предложений */
        $dbal->leftJoin(
            'product_offer',
            ProductOfferImage::class,
            'product_offer_images',
            '
			
			product_offer_images.offer = product_offer.id
			
		',
        );

        /* Фото продукта */
        $dbal->leftJoin(
            'product',
            ProductPhoto::class,
            'product_photo',
            '
	
			product_photo.event = product.event
			',
        );


        $dbal->addSelect(
            "JSON_AGG ( DISTINCT JSONB_BUILD_OBJECT
                (
                    'product_img_root', ya_market_product_images.root,
                    'product_img', CONCAT ( '/upload/".$dbal->table(YandexMarketProductCustomImage::class)."' , '/', ya_market_product_images.name),
                    'product_img_ext', ya_market_product_images.ext,
                    'product_img_cdn', ya_market_product_images.cdn
                )

			) FILTER (WHERE ya_market_product_images.ext IS NOT NULL)
			
			AS product_images_custom
	    ");


        $dbal->addSelect(
            "JSON_AGG ( DISTINCT JSONB_BUILD_OBJECT
					(
						'product_img_root', product_modification_image.root,
						'product_img', CONCAT ( '/upload/".$dbal->table(ProductModificationImage::class)."' , '/', product_modification_image.name),
						'product_img_ext', product_modification_image.ext,
						'product_img_cdn', product_modification_image.cdn
					)

			) FILTER (WHERE product_modification_image.ext IS NOT NULL)
			
			AS product_images_modification
	    ");


        $dbal->addSelect(
            "JSON_AGG ( DISTINCT JSONB_BUILD_OBJECT
					(
						'product_img_root', product_variation_image.root,
						'product_img', CONCAT ( '/upload/".$dbal->table(ProductVariationImage::class)."' , '/', product_variation_image.name),
						'product_img_ext', product_variation_image.ext,
						'product_img_cdn', product_variation_image.cdn
					)

			) FILTER (WHERE product_variation_image.ext IS NOT NULL)
			
			AS product_images_variation
	    ");


        $dbal->addSelect(
            "JSON_AGG ( DISTINCT JSONB_BUILD_OBJECT
					(
						'product_img_root', product_offer_images.root,
						'product_img', CONCAT ( '/upload/".$dbal->table(ProductOfferImage::class)."' , '/', product_offer_images.name),
						'product_img_ext', product_offer_images.ext,
						'product_img_cdn', product_offer_images.cdn
					) 

			) FILTER (WHERE product_offer_images.ext IS NOT NULL)
			
			AS product_images_offer
	    ");


        $dbal->addSelect(
            "JSON_AGG ( DISTINCT JSONB_BUILD_OBJECT
					(
						'product_img_root', product_photo.root,
						'product_img', CONCAT ( '/upload/".$dbal->table(ProductPhoto::class)."' , '/', product_photo.name),
						'product_img_ext', product_photo.ext,
						'product_img_cdn', product_photo.cdn
					)

			) FILTER (WHERE product_photo.ext IS NOT NULL)
			
			AS product_images
	    ");


        /* Базовая Цена товара */
        $dbal->leftJoin(
            'product',
            ProductPrice::class,
            'product_price',
            'product_price.event = product.event',
        );

        /* Цена торгового предложения */
        $dbal->leftJoin(
            'product_offer',
            ProductOfferPrice::class,
            'product_offer_price',
            'product_offer_price.offer = product_offer.id',
        );

        /* Цена множественного варианта */
        $dbal->leftJoin(
            'product_variation',
            ProductVariationPrice::class,
            'product_variation_price',
            'product_variation_price.variation = product_variation.id',
        );


        /* Цена модификации множественного варианта */
        $dbal->leftJoin(
            'product_modification',
            ProductModificationPrice::class,
            'product_modification_price',
            'product_modification_price.modification = product_modification.id',
        );

        /* Стоимость продукта */

        $dbal->addSelect('
			COALESCE(
                NULLIF(product_modification_price.price, 0), 
                NULLIF(product_variation_price.price, 0), 
                NULLIF(product_offer_price.price, 0), 
                NULLIF(product_price.price, 0),
                0
            ) AS product_price
		');

        /* Предыдущая стоимость продукта */

        $dbal->addSelect("
			COALESCE(
                NULLIF(product_modification_price.old, 0),
                NULLIF(product_variation_price.old, 0),
                NULLIF(product_offer_price.old, 0),
                NULLIF(product_price.old, 0),
                0
            ) AS product_old_price
		");

        /* Валюта продукта */

        $dbal->addSelect(
            '
			COALESCE(
                CASE WHEN product_modification_price.price IS NOT NULL AND product_modification_price.price > 0 
                     THEN product_modification_price.currency END, 
                     
                CASE WHEN product_variation_price.price IS NOT NULL AND product_variation_price.price > 0 
                     THEN product_variation_price.currency END, 
                     
                CASE WHEN product_offer_price.price IS NOT NULL AND product_offer_price.price > 0 
                     THEN product_offer_price.currency END, 
                     
                CASE WHEN product_price.price IS NOT NULL AND product_price.price > 0 
                     THEN product_price.currency END
            ) AS product_currency
		',
        );

        /**
         * Применяем настройки стоимости магазина
         */

        if($dbal->bindProjectProfile())
        {
            $dbal
                ->addSelect('project_profile_info.discount AS project_discount')
                ->leftJoin(
                    'product',
                    UserProfileInfo::class,
                    'project_profile_info',
                    'project_profile_info.profile = :project_profile',
                );
        }


        /**
         * Наличие продукции на складе
         * Если подключен модуль складского учета и передан идентификатор профиля
         */

        if(true === ($this->profile instanceof UserProfileUid) && class_exists(BaksDevProductsStocksBundle::class))
        {

            $dbal
                ->addSelect('(SUM(stock.total) - SUM(stock.reserve)) AS product_quantity')
                ->leftJoin(
                    'product',
                    ProductStockTotal::class,
                    'stock',
                    '
                    stock.profile = :profile AND
                    stock.product = product.id 
                    
                    AND
                        
                        CASE 
                            WHEN product_offer.const IS NOT NULL 
                            THEN stock.offer = product_offer.const
                            ELSE stock.offer IS NULL
                        END
                            
                    AND 
                    
                        CASE
                            WHEN product_variation.const IS NOT NULL 
                            THEN stock.variation = product_variation.const
                            ELSE stock.variation IS NULL
                        END
                        
                    AND
                    
                        CASE
                            WHEN product_modification.const IS NOT NULL 
                            THEN stock.modification = product_modification.const
                            ELSE stock.modification IS NULL
                        END
                    
                    
                ',
                )
                ->setParameter(
                    'profile',
                    $this->profile,
                    UserProfileUid::TYPE,
                );

        }

        /**
         * Наличие продукции в карточке
         */

        else
        {

            /* Наличие и резерв торгового предложения */
            $dbal->leftJoin(
                'product_offer',
                ProductOfferQuantity::class,
                'product_offer_quantity',
                'product_offer_quantity.offer = product_offer.id',
            );

            /* Наличие и резерв множественного варианта */
            $dbal->leftJoin(
                'product_variation',
                ProductVariationQuantity::class,
                'product_variation_quantity',
                'product_variation_quantity.variation = product_variation.id',
            );

            /* Наличие и резерв модификации множественного варианта */
            $dbal->leftJoin(
                'product_modification',
                ProductModificationQuantity::class,
                'product_modification_quantity',
                'product_modification_quantity.modification = product_modification.id',
            );

            $dbal->addSelect('
                COALESCE(
                    CASE WHEN product_modification_quantity.quantity > 0 AND product_modification_quantity.quantity > product_modification_quantity.reserve 
                         THEN product_modification_quantity.quantity - ABS(product_modification_quantity.reserve) END,
                    CASE WHEN product_variation_quantity.quantity > 0 AND product_variation_quantity.quantity > product_variation_quantity.reserve 
                         THEN product_variation_quantity.quantity - ABS(product_variation_quantity.reserve) END,
                    CASE WHEN product_offer_quantity.quantity > 0 AND product_offer_quantity.quantity > product_offer_quantity.reserve 
                         THEN product_offer_quantity.quantity - ABS(product_offer_quantity.reserve) END,
                    CASE WHEN product_price.quantity > 0 AND product_price.quantity > product_price.reserve 
                         THEN product_price.quantity - ABS(product_price.reserve) END
                ) AS product_quantity
		    ');

        }


        /** Артикул продукта */

        $dbal->addSelect('
            COALESCE(
                product_modification.article, 
                product_variation.article, 
                product_offer.article, 
                product_info.article
            ) AS article
		');

        /** Штрихкод продукта */

        $dbal->addSelect('
            COALESCE(
                product_modification.barcode, 
                product_variation.barcode, 
                product_offer.barcode, 
                product_info.barcode,
                NULL
            ) AS barcode
		');

        $dbal->allGroupByExclude();

        return $dbal
            ->enableCache('products-product')
            ->fetchHydrate(CurrentYaMarketProductCardResult::class) ?: false;
    }

}
