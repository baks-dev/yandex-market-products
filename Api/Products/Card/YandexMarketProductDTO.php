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

namespace BaksDev\Yandex\Market\Products\Api\Products\Card;

use BaksDev\Reference\Currency\Type\Currencies\RUR;
use BaksDev\Reference\Currency\Type\Currency;
use BaksDev\Reference\Money\Type\Money;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Symfony\Component\Validator\Constraints as Assert;

final class YandexMarketProductDTO
{
    private UserProfileUid $profile;

    /** Артикул товара */
    #[Assert\Blank]
    private string $article;

    /** Название */
    private string $name;

    /** Название бренда или производителя */
    private string $vendor;

    /** Название бренда или производителя */
    private string $barcode;

    /** Подробное описание товара */
    private string $desc;

    /** Страна, где был произведен товар. */
    private string|bool $country;

    /** Стоимость товара */
    private Money $price;

    /** Валюта */
    private Currency $currency;

    private array $pictures;

    private array $params;


    public function __construct(UserProfileUid $profile, array $data)
    {


        $this->pictures = $data['pictures'];
        $this->params = $data['params'] ?? [];

        $this->profile = $profile;
        $this->article = $data['offerId'];
        $this->name = $data['name'];
        $this->vendor = $data['vendor'];
        $this->desc = $data['description'];
        $this->country = current($data['manufacturerCountries']);
        $this->barcode = current($data['barcodes']);

        $basicPrice = $data['basicPrice'] ?? ['value' => 0, 'currencyId' => null];

        $this->price = new Money($basicPrice['value']);
        $this->currency = new Currency($basicPrice['currencyId']);


    }

    /**
     * Name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Profile
     */
    public function getProfile(): UserProfileUid
    {
        return $this->profile;
    }

    /**
     * Article
     */
    public function getArticle(): string
    {
        return $this->article;
    }

    /**
     * Vendor
     */
    public function getVendor(): string
    {
        return $this->vendor;
    }

    /**
     * Barcode
     */
    public function getBarcode(): string
    {
        return $this->barcode;
    }

    /**
     * Desc
     */
    public function getDesc(): string
    {
        return $this->desc;
    }

    /**
     * Country
     */
    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * Price
     */
    public function getPrice(): Money
    {
        return $this->price;
    }

    /**
     * Currency
     */
    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    public function equals(array $request): bool
    {
        if($request['name'] !== $this->name)
        {
            return false;
        }

        if($request['vendor'] !== $this->vendor)
        {
            return false;
        }




        if(!in_array($this->barcode, $request['barcodes'], true))
        {
            return false;
        }

        if(!in_array($this->country, $request['manufacturerCountries'], true))
        {
            return false;
        }

        /** Проверка изображений */

        if(count($request['pictures']) !== count($this->pictures))
        {
            return false;
        }

        foreach($request['pictures'] as $picture)
        {
            if(!in_array($picture, $this->pictures, true))
            {
                return false;
            }
        }

        /** Проверка параметров */

        if(count($request['params']) !== count($this->params))
        {
            return false;
        }

        foreach($request['params'] as $param)
        {
            if(!in_array($param, $this->params, false))
            {
                return false;
            }
        }

        return true;
    }


}