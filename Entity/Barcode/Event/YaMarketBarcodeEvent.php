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

namespace BaksDev\Yandex\Market\Products\Entity\Barcode\Event;

use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Yandex\Market\Products\Entity\Barcode\Event\Counter\YaMarketBarcodeCounter;
use BaksDev\Yandex\Market\Products\Entity\Barcode\Event\Custom\YaMarketBarcodeCustom;
use BaksDev\Yandex\Market\Products\Entity\Barcode\Event\Modification\YaMarketBarcodeModification;
use BaksDev\Yandex\Market\Products\Entity\Barcode\Event\Modify\YaMarketBarcodeModify;
use BaksDev\Yandex\Market\Products\Entity\Barcode\Event\Name\YaMarketBarcodeName;
use BaksDev\Yandex\Market\Products\Entity\Barcode\Event\Offer\YaMarketBarcodeOffer;
use BaksDev\Yandex\Market\Products\Entity\Barcode\Event\Property\YaMarketBarcodeProperty;
use BaksDev\Yandex\Market\Products\Entity\Barcode\Event\Variation\YaMarketBarcodeVariation;
use BaksDev\Yandex\Market\Products\Entity\Barcode\YaMarketBarcode;
use BaksDev\Yandex\Market\Products\Type\Barcode\Event\YaMarketBarcodeEventUid;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'ya_market_barcode_event')]
class YaMarketBarcodeEvent extends EntityEvent
{
    /** ID События */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\Column(type: YaMarketBarcodeEventUid::TYPE)]
    private YaMarketBarcodeEventUid $id;

    /**
     * ID категории
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Column(type: CategoryProductUid::TYPE)]
    private CategoryProductUid $main;

    /** Модификатор */
    #[Assert\Valid]
    #[ORM\OneToOne(targetEntity: YaMarketBarcodeModify::class, mappedBy: 'event', cascade: ['all'], fetch: 'EAGER')]
    private YaMarketBarcodeModify $modify;

    /** Количество стикеров */
    #[ORM\OneToOne(targetEntity: YaMarketBarcodeCounter::class, mappedBy: 'event', cascade: ['all'], fetch: 'EAGER')]
    private ?YaMarketBarcodeCounter $counter = null;

    /** Флаг отображения звания в стикере */
    #[ORM\OneToOne(targetEntity: YaMarketBarcodeName::class, mappedBy: 'event', cascade: ['all'], fetch: 'EAGER')]
    private ?YaMarketBarcodeName $name = null;

    /** Добавить Торговое предложение в стикер */
    #[ORM\OneToOne(targetEntity: YaMarketBarcodeOffer::class, mappedBy: 'event', cascade: ['all'], fetch: 'EAGER')]
    private ?YaMarketBarcodeOffer $offer = null;

    /** Добавить Множественный вариант в стикер */
    #[ORM\OneToOne(targetEntity: YaMarketBarcodeVariation::class, mappedBy: 'event', cascade: ['all'], fetch: 'EAGER')]
    private ?YaMarketBarcodeVariation $variation = null;

    /** Добавить модификацию множественного варианта в стикер */
    #[ORM\OneToOne(targetEntity: YaMarketBarcodeModification::class, mappedBy: 'event', cascade: ['all'], fetch: 'EAGER')]
    private ?YaMarketBarcodeModification $modification = null;

    /** Коллекция свойств продукта */
    #[Assert\Valid]
    #[ORM\OrderBy(['sort' => 'ASC'])]
    #[ORM\OneToMany(targetEntity: YaMarketBarcodeProperty::class, mappedBy: 'event', cascade: ['all'], fetch: 'EAGER')]
    private Collection $property;

    /** Коллекция пользовательских свойств */
    #[Assert\Valid]
    #[ORM\OneToMany(targetEntity: YaMarketBarcodeCustom::class, mappedBy: 'event', cascade: ['all'], fetch: 'EAGER')]
    private Collection $custom;

    public function __construct()
    {
        $this->id = new YaMarketBarcodeEventUid();
        $this->modify = new YaMarketBarcodeModify($this);
    }

    public function __clone()
    {
        $this->id = clone $this->id;
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }

    public function getId(): YaMarketBarcodeEventUid
    {
        return $this->id;
    }

    public function getMain(): CategoryProductUid
    {
        return $this->main;
    }

    public function setMain(YaMarketBarcode|CategoryProductUid $main): self  // TODO?
    {
        if($main instanceof YaMarketBarcode)
        {
            return $this;
        }

        $this->main = $main;

        return $this;
    }

    public function getDto($dto): mixed
    {
        $dto = is_string($dto) && class_exists($dto) ? new $dto() : $dto;

        if($dto instanceof YaMarketBarcodeEventInterface)
        {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }


    public function setEntity($dto): mixed
    {
        if($dto instanceof YaMarketBarcodeEventInterface || $dto instanceof self)
        {
            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }
}