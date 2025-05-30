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

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use BaksDev\Yandex\Market\Products\BaksDevYandexMarketProductsBundle;
use BaksDev\Yandex\Market\Products\Type\Card\Event\YaMarketProductsCardEventType;
use BaksDev\Yandex\Market\Products\Type\Card\Event\YaMarketProductsCardEventUid;
use BaksDev\Yandex\Market\Products\Type\Card\Id\YaMarketProductsCardType;
use BaksDev\Yandex\Market\Products\Type\Card\Id\YaMarketProductsCardUid;
use BaksDev\Yandex\Market\Products\Type\Id\YandexMarketProductType;
use BaksDev\Yandex\Market\Products\Type\Id\YandexMarketProductUid;
use BaksDev\Yandex\Market\Products\Type\Image\YandexMarketProductImageType;
use BaksDev\Yandex\Market\Products\Type\Image\YandexMarketProductImageUid;
use BaksDev\Yandex\Market\Products\Type\Settings\Event\YaMarketProductsSettingsEventType;
use BaksDev\Yandex\Market\Products\Type\Settings\Event\YaMarketProductsSettingsEventUid;
use BaksDev\Yandex\Market\Products\Type\Settings\Property\YaMarketProductProperty;
use BaksDev\Yandex\Market\Products\Type\Settings\Property\YaMarketProductPropertyType;
use Symfony\Config\DoctrineConfig;

return static function(DoctrineConfig $doctrine): void {

    $doctrine->dbal()->type(YaMarketProductsSettingsEventUid::TYPE)->class(YaMarketProductsSettingsEventType::class);
    $doctrine->dbal()->type(YaMarketProductProperty::TYPE)->class(YaMarketProductPropertyType::class);

    $doctrine->dbal()->type(YaMarketProductsCardUid::TYPE)->class(YaMarketProductsCardType::class);
    $doctrine->dbal()->type(YaMarketProductsCardEventUid::TYPE)->class(YaMarketProductsCardEventType::class);

    $doctrine->dbal()->type(YandexMarketProductUid::TYPE)->class(YandexMarketProductType::class);
    $doctrine->dbal()->type(YandexMarketProductImageUid::TYPE)->class(YandexMarketProductImageType::class);


    $emDefault = $doctrine->orm()->entityManager('default')->autoMapping(true);


    $emDefault->mapping('yandex-market-products')
        ->type('attribute')
        ->dir(BaksDevYandexMarketProductsBundle::PATH.'Entity')
        ->isBundle(false)
        ->prefix(BaksDevYandexMarketProductsBundle::NAMESPACE.'\\Entity')
        ->alias('yandex-market-products');
};
