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

namespace BaksDev\Yandex\Market\Products\Repository\Card\AllYaMarketProductsCard;

use BaksDev\Avito\Board\Entity\AvitoBoard;
use BaksDev\Avito\Board\Entity\Event\AvitoBoardEvent;
use BaksDev\Avito\Products\Entity\AvitoProduct;
use BaksDev\Avito\Products\Entity\Images\AvitoProductImage;
use BaksDev\Avito\Products\Forms\AvitoFilter\AvitoProductsFilterDTO;
use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Core\Services\Paginator\PaginatorInterface;
use BaksDev\Elastic\Api\Index\ElasticGetIndex;
use BaksDev\Products\Category\Entity\CategoryProduct;
use BaksDev\Products\Category\Entity\Info\CategoryProductInfo;
use BaksDev\Products\Category\Entity\Offers\CategoryProductOffers;
use BaksDev\Products\Category\Entity\Offers\Variation\CategoryProductVariation;
use BaksDev\Products\Category\Entity\Offers\Variation\Modification\CategoryProductModification;
use BaksDev\Products\Category\Entity\Trans\CategoryProductTrans;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Product\Entity\Active\ProductActive;
use BaksDev\Products\Product\Entity\Category\ProductCategory;
use BaksDev\Products\Product\Entity\Event\ProductEvent;
use BaksDev\Products\Product\Entity\Info\ProductInfo;
use BaksDev\Products\Product\Entity\Offers\Image\ProductOfferImage;
use BaksDev\Products\Product\Entity\Offers\Price\ProductOfferPrice;
use BaksDev\Products\Product\Entity\Offers\ProductOffer;
use BaksDev\Products\Product\Entity\Offers\Variation\Image\ProductVariationImage;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\Price\ProductModificationPrice;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\ProductModification;
use BaksDev\Products\Product\Entity\Offers\Variation\Price\ProductVariationPrice;
use BaksDev\Products\Product\Entity\Offers\Variation\ProductVariation;
use BaksDev\Products\Product\Entity\Photo\ProductPhoto;
use BaksDev\Products\Product\Entity\Product;
use BaksDev\Products\Product\Entity\Property\ProductProperty;
use BaksDev\Products\Product\Entity\Trans\ProductTrans;
use BaksDev\Products\Product\Forms\ProductFilter\Admin\ProductFilterDTO;
use BaksDev\Products\Product\Forms\ProductFilter\Admin\Property\ProductFilterPropertyDTO;


interface AllYaMarketProductsCardInterface
{
    public function findPaginator(): PaginatorInterface;
}

final class AllYaMarketProductsCard implements AllYaMarketProductsCardInterface
{
    private ?ProductFilterDTO $filter = null;

    private ?AvitoProductsFilterDTO $avitoProductsFilter = null;

    private ?SearchDTO $search = null;

    public function __construct(
        private readonly DBALQueryBuilder $DBALQueryBuilder,
        private readonly PaginatorInterface $paginator,
        private readonly ?ElasticGetIndex $elasticGetIndex = null,
    ) {}

    public function search(SearchDTO $search): self
    {
        $this->search = $search;
        return $this;
    }

    public function filter(ProductFilterDTO $filter): self
    {
        $this->filter = $filter;
        return $this;
    }

    public function filterAvitoProducts(AvitoProductsFilterDTO $avitoProductsFilter): self
    {
        $this->avitoProductsFilter = $avitoProductsFilter;
        return $this;
    }

    public function findPaginator(): PaginatorInterface
    {
        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $dbal
            ->select('product.id')
            ->addSelect('product.event')
            ->from(Product::class, 'product');

        $dbal->join(
            'product',
            ProductEvent::class,
            'product_event',
            'product_event.id = product.event'
        );

        /** Только активные продукты */
        $dbal
            ->join(
                'product',
                ProductActive::class,
                'product_active',
                '
                    product_active.event = product.event AND
                    product_active.active IS TRUE'
            );

        /** Название продукта */
        $dbal
            ->addSelect('product_trans.name AS product_name')
            ->leftJoin(
                'product_event',
                ProductTrans::class,
                'product_trans',
                '
                    product_trans.event = product_event.id AND 
                    product_trans.local = :local'
            );

        /** Здесь основной артикул товара */
        $dbal
            ->leftJoin(
                'product_event',
                ProductInfo::class,
                'product_info',
                'product_info.product = product.id'
            );

        /**
         * ТОРГОВОЕ ПРЕДЛОЖЕНИЕ
         */
        $dbal
            ->addSelect('product_offer.id as product_offer_id')
            ->addSelect('product_offer.value as product_offer_value')
            ->addSelect('product_offer.const as product_offer_const')
            ->addSelect('product_offer.postfix as product_offer_postfix')
            ->leftJoin(
                'product_event',
                ProductOffer::class,
                'product_offer',
                'product_offer.event = product_event.id'
            );


        /** ФИЛЬТР по торговому предложения */
        if($this->filter?->getOffer())
        {
            $dbal->andWhere('product_offer.value = :offer');
            $dbal->setParameter('offer', $this->filter->getOffer());
        }

        /** ЦЕНА торгового предложения */
        $dbal->leftJoin(
            'product_offer',
            ProductOfferPrice::class,
            'product_offer_price',
            'product_offer_price.offer = product_offer.id'
        );

        /** ТИП торгового предложения */
        $dbal
            ->addSelect('category_offer.reference as product_offer_reference')
            ->leftJoin(
                'product_offer',
                CategoryProductOffers::class,
                'category_offer',
                'category_offer.id = product_offer.category_offer'
            );

        /**
         * ВАРИАНТЫ торгового предложения
         */
        $dbal
            ->addSelect('product_variation.id as product_variation_id')
            ->addSelect('product_variation.const as product_variation_const')
            ->addSelect('product_variation.value as product_variation_value')
            ->addSelect('product_variation.postfix as product_variation_postfix')
            ->leftJoin(
                'product_offer',
                ProductVariation::class,
                'product_variation',
                'product_variation.offer = product_offer.id'
            );

        /** ФИЛЬТР по множественным вариантам */
        if($this->filter?->getVariation())
        {
            $dbal->andWhere('product_variation.value = :variation');
            $dbal->setParameter('variation', $this->filter->getVariation());
        }


        /** ЦЕНА множественных вариантов */
        $dbal->leftJoin(
            'product_variation',
            ProductVariationPrice::class,
            'product_variation_price',
            'product_variation_price.variation = product_variation.id'
        );

        /** ТИП множественного варианта торгового предложения */
        $dbal
            ->addSelect('category_offer_variation.reference as product_variation_reference')
            ->leftJoin(
                'product_variation',
                CategoryProductVariation::class,
                'category_offer_variation',
                'category_offer_variation.id = product_variation.category_variation'
            );

        /**
         * МОДИФИКАЦИИ множественного варианта
         */
        $dbal
            ->addSelect('product_modification.id as product_modification_id')
            ->addSelect('product_modification.const as product_modification_const')
            ->addSelect('product_modification.value as product_modification_value')
            ->addSelect('product_modification.postfix as product_modification_postfix')
            ->leftJoin(
                'product_variation',
                ProductModification::class,
                'product_modification',
                'product_modification.variation = product_variation.id '
            );

        /** ФИЛЬТР по модификациям множественного варианта */
        if($this->filter?->getModification())
        {
            $dbal->andWhere('product_modification.value = :modification');
            $dbal->setParameter('modification', $this->filter->getModification());
        }

        /** ЦЕНА модификации множественного варианта */
        $dbal->leftJoin(
            'product_modification',
            ProductModificationPrice::class,
            'product_modification_price',
            'product_modification_price.modification = product_modification.id'
        );

        /** ТИП модификации множественного варианта */
        $dbal->addSelect('category_offer_modification.reference as product_modification_reference');
        $dbal->leftJoin(
            'product_modification',
            CategoryProductModification::class,
            'category_offer_modification',
            'category_offer_modification.id = product_modification.category_modification'
        );

        /**
         * Артикул продукта
         */
        $dbal->addSelect('
            COALESCE(
                product_modification.article, 
                product_variation.article, 
                product_offer.article, 
                product_info.article
            ) AS product_article
		');

        /**
         * Все фото
         */
        $dbal->leftJoin(
            'product_event',
            ProductPhoto::class,
            'product_photo',
            '
                product_photo.event = product_event.id AND
                product_photo.root = true'
        );

        $dbal->leftJoin(
            'product_offer',
            ProductVariationImage::class,
            'product_variation_image',
            '
                product_variation_image.variation = product_variation.id AND
                product_variation_image.root = true'
        );

        $dbal->leftJoin(
            'product_offer',
            ProductOfferImage::class,
            'product_offer_images',
            '
                product_offer_images.offer = product_offer.id AND
                product_offer_images.root = true'
        );

        $dbal->addSelect(
            "
			CASE
			   WHEN product_variation_image.name IS NOT NULL THEN
					CONCAT ( '/upload/".$dbal->table(ProductVariationImage::class)."' , '/', product_variation_image.name)
			   WHEN product_offer_images.name IS NOT NULL THEN
					CONCAT ( '/upload/".$dbal->table(ProductOfferImage::class)."' , '/', product_offer_images.name)
			   WHEN product_photo.name IS NOT NULL THEN
					CONCAT ( '/upload/".$dbal->table(ProductPhoto::class)."' , '/', product_photo.name)
			   ELSE NULL
			END AS product_image
		"
        );

        /** Расширение изображения */
        $dbal->addSelect(
            '
            COALESCE(
                product_variation_image.ext,
                product_offer_images.ext,
                product_photo.ext
            ) AS product_image_ext'
        );

        /** Флаг загрузки файла CDN */
        $dbal->addSelect(
            '
            COALESCE(
                product_variation_image.cdn,
                product_offer_images.cdn,
                product_photo.cdn
            ) AS product_image_cdn'
        );


        /** Продукт Авито */
        $dbal
            ->addSelect('avito_product.id as avito_product_id')
            ->leftJoin(
                'product_modification',
                AvitoProduct::class,
                'avito_product',
                '
                avito_product.product = product.id AND 
                (avito_product.offer IS NULL OR avito_product.offer = product_offer.const) AND 
                (avito_product.variation IS NULL OR avito_product.variation = product_variation.const) AND 
                (avito_product.modification IS NULL OR avito_product.modification = product_modification.const)
            '
            );

        /** Изображения Авито */
        $dbal->leftJoin(
            'avito_product',
            AvitoProductImage::class,
            'avito_product_images',
            '
                avito_product_images.avito = avito_product.id AND
                avito_product_images.root = true'
        );

        $dbal->addSelect(
            "
            CASE
                WHEN avito_product_images.name IS NOT NULL THEN
                    CONCAT ( '/upload/".$dbal->table(AvitoProductImage::class)."' , '/', avito_product_images.name)
                ELSE NULL 
            END as avito_product_image
            "
        );

        /** Расширение изображения */
        $dbal->addSelect('
			CASE
			   WHEN avito_product_images.name IS NOT NULL THEN
					avito_product_images.ext
			   ELSE NULL
			END AS avito_product_image_ext
		');

        /** Флаг загрузки файла CDN */
        $dbal->addSelect('
			CASE
			   WHEN avito_product_images.name IS NOT NULL THEN
					avito_product_images.cdn
			   ELSE NULL
			END AS avito_product_image_cdn
		');


        /**
         * Категория
         */
        $dbal
            ->join(
                'product_event',
                ProductCategory::class,
                'product_category',
                '
                    product_category.event = product_event.id AND 
                    product_category.root = true'
            );

        if($this->filter?->getCategory())
        {
            $dbal->andWhere('product_category.category = :category');
            $dbal->setParameter('category', $this->filter->getCategory(), CategoryProductUid::TYPE);
        }

        $dbal->join(
            'product_category',
            CategoryProduct::class,
            'category',
            'category.id = product_category.category'
        );

        /** Только активные разделы */
        $dbal
            ->addSelect('category_info.active as category_active')
            ->join(
                'product_category',
                CategoryProductInfo::class,
                'category_info',
                '
                    category.event = category_info.event AND
                    category_info.active IS TRUE'
            );

        $dbal
            ->addSelect('category_trans.name AS category_name')
            ->leftJoin(
                'category',
                CategoryProductTrans::class,
                'category_trans',
                '
                    category_trans.event = category.event AND 
                    category_trans.local = :local'
            );

        /** Avito mapper */
        /**
         * Только те продукты, для которых создан маппер
         */
        $dbal
            ->addSelect('avito_board.id AS avito_board_mapper_category_id')
            ->join(
                'product_category',
                AvitoBoard::class,
                'avito_board',
                'avito_board.id = product_category.category'
            );

        /**
         * Название категории в Авито из активного события маппера. Для каждой карточки
         */
        $dbal
            ->addSelect('avito_board_event.avito AS avito_board_avito_category')
            ->join(
                'avito_board',
                AvitoBoardEvent::class,
                'avito_board_event',
                'avito_board_event.id = avito_board.event'
            );

        /**
         * Фильтр по свойства продукта
         */
        if($this->filter?->getProperty())
        {
            /** @var ProductFilterPropertyDTO $property */
            foreach($this->filter->getProperty() as $property)
            {
                if($property->getValue())
                {
                    $dbal->join(
                        'product',
                        ProductProperty::class,
                        'product_property_'.$property->getType(),
                        'product_property_'.$property->getType().'.event = product.event AND
                        product_property_'.$property->getType().'.field = :'.$property->getType().'_const AND
                        product_property_'.$property->getType().'.value = :'.$property->getType().'_value'
                    );

                    $dbal->setParameter($property->getType().'_const', $property->getConst());
                    $dbal->setParameter($property->getType().'_value', $property->getValue());
                }
            }
        }


        if($this->avitoProductsFilter->getExists() !== null)
        {
            if($this->avitoProductsFilter->getExists() === true)
            {
                $dbal->andWhere('avito_product_images.name IS NOT NULL');
            }
            else
            {
                $dbal->andWhere('avito_product_images.name IS NULL');
            }
        }


        if($this->search?->getQuery())
        {
            /** Поиск по модификации */
            $result = $this->elasticGetIndex ? $this->elasticGetIndex->handle(ProductModification::class, $this->search->getQuery(), 1) : false;

            if($result)
            {
                $counter = $result['hits']['total']['value'];

                if($counter)
                {
                    /** Идентификаторы */
                    $data = array_column($result['hits']['hits'], "_source");

                    $dbal
                        ->createSearchQueryBuilder($this->search)
                        ->addSearchInArray('product_modification.id', array_column($data, "id"));

                    return $this->paginator->fetchAllAssociative($dbal);
                }

                /** Поиск по продукции */
                $result = $this->elasticGetIndex->handle(Product::class, $this->search->getQuery(), 1);

                $counter = $result['hits']['total']['value'];

                if($counter)
                {
                    /** Идентификаторы */
                    $data = array_column($result['hits']['hits'], "_source");

                    $dbal
                        ->createSearchQueryBuilder($this->search)
                        ->addSearchInArray('product.id', array_column($data, "id"));

                    return $this->paginator->fetchAllAssociative($dbal);
                }
            }

            $dbal
                ->createSearchQueryBuilder($this->search)
                ->addSearchEqualUid('account.id')
                ->addSearchEqualUid('account.event')
                ->addSearchLike('product_trans.name')
                ->addSearchLike('product_info.article')
                ->addSearchLike('product_offer.article')
                ->addSearchLike('product_modification.article')
                ->addSearchLike('product_variation.article');

        }

        return $this->paginator->fetchAllAssociative($dbal);
    }

}