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

namespace BaksDev\Yandex\Market\Products\UseCase\Cards\NewEdit;

use BaksDev\Yandex\Market\Products\Entity\Card\Event\YaMarketProductsCardEventInterface;
use BaksDev\Yandex\Market\Products\Type\Card\Event\YaMarketProductsCardEventUid;
use BaksDev\Yandex\Market\Products\UseCase\Card\NewEdit\Market\YaMarketProductsCardMarketDTO;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/** @see YaMarketProductsCardEvent */
final class YaMarketProductsCardDTO implements YaMarketProductsCardEventInterface
{
    /**
     * Идентификатор события
     */
    #[Assert\Uuid]
    private ?YaMarketProductsCardEventUid $id = null;

    /**
     * Идентификатор продукции Ya Market
     */
    #[Assert\Valid]
    private Market\YaMarketProductsCardMarketDTO $market;

    /**
     * Параметры карточки
     */
    #[Assert\Valid]
    private ArrayCollection $parameters;

    /**
     * Коллекция Свойств
     */
    #[Assert\Valid]
    private ArrayCollection $property;

    public function __construct()
    {
        $this->property = new ArrayCollection;
        $this->parameters = new ArrayCollection;
        $this->market = new Market\YaMarketProductsCardMarketDTO();
    }


    /**
     * Идентификатор события
     */
    public function getEvent(): ?YaMarketProductsCardEventUid
    {
        return $this->id;
    }


    /**
     * Market
     */
    public function getMarket(): Market\YaMarketProductsCardMarketDTO
    {
        return $this->market;
    }

    public function setMarket(Market\YaMarketProductsCardMarketDTO $market): self
    {
        $this->market = $market;
        return $this;
    }



    /* PARAMS */

    public function getParameters(): ArrayCollection
    {
        return $this->parameters;
    }


    public function addParameter(Parameters\YaMarketProductsCardParametersDTO $parameters): void
    {
        $filter = $this->parameters->filter(
            function(Parameters\YaMarketProductsCardParametersDTO $element) use ($parameters) {
                return $parameters->getType() === $element->getType();
            });

        if($filter->isEmpty())
        {
            $this->parameters->add($parameters);
        }
    }


    public function removeParameter(Parameters\YaMarketProductsCardParametersDTO $parameters): void
    {
        $this->parameters->removeElement($parameters);
    }


    /* PROPERTY */

    public function getProperty(): ArrayCollection
    {
        return $this->property;
    }


    public function addProperty(Property\YaMarketProductsCardPropertyDTO $property): void
    {
        $filter = $this->property->filter(
            function(Property\YaMarketProductsCardPropertyDTO $element) use ($property) {
                return $property->getType()?->equals($element->getType());
            });

        if($filter->isEmpty())
        {
            $this->property->add($property);
        }

    }


    public function removeProperty(Property\YaMarketProductsCardPropertyDTO $property): void
    {
        $this->property->removeElement($property);
    }
}