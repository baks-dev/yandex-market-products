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

namespace BaksDev\Yandex\Market\Products\Entity\Card\Event;

use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Yandex\Market\Products\Entity\Card\Market\YaMarketProductsCardMarket;
use BaksDev\Yandex\Market\Products\Entity\Card\Modify\YaMarketProductsCardModify;
use BaksDev\Yandex\Market\Products\Entity\Card\Parameters\YaMarketProductsCardParameters;
use BaksDev\Yandex\Market\Products\Entity\Card\Property\YaMarketProductsCardProperty;
use BaksDev\Yandex\Market\Products\Entity\Card\YaMarketProductsCard;
use BaksDev\Yandex\Market\Products\Type\Card\Event\YaMarketProductsCardEventUid;
use BaksDev\Yandex\Market\Products\Type\Card\Id\YaMarketProductsCardUid;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

/* YaMarketProductsCardEvent */

#[ORM\Entity]
#[ORM\Table(name: 'ya_market_products_card_event')]
class YaMarketProductsCardEvent extends EntityEvent
{
    /**
     * Идентификатор События
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\Column(type: YaMarketProductsCardEventUid::TYPE)]
    private YaMarketProductsCardEventUid $id;

    /**
     * Идентификатор YaMarketProductsCard
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Column(type: YaMarketProductsCardUid::TYPE, nullable: false)]
    private ?YaMarketProductsCardUid $main = null;

    /**
     * Модификатор
     */
    #[ORM\OneToOne(targetEntity: YaMarketProductsCardModify::class, mappedBy: 'event', cascade: ['all'])]
    private YaMarketProductsCardModify $modify;


    /**
     * Свойства карточки Маркет
     */
    #[Assert\Valid]
    #[ORM\OneToMany(targetEntity: YaMarketProductsCardProperty::class, mappedBy: 'event', cascade: ['all'])]
    private Collection $property;


    /**
     * Параметры продукции категории Маркет
     */
    #[Assert\Valid]
    #[ORM\OneToMany(targetEntity: YaMarketProductsCardParameters::class, mappedBy: 'event', cascade: ['all'])]
    private Collection $parameters;

    /**
     * Информация о продукции
     */
    #[ORM\OneToOne(targetEntity: YaMarketProductsCardMarket::class, mappedBy: 'event', cascade: ['all'])]
    private ?YaMarketProductsCardMarket $market = null;


    public function __construct()
    {
        $this->id = new YaMarketProductsCardEventUid();
        $this->modify = new YaMarketProductsCardModify($this);
    }

    /**
     * Идентификатор События
     */

    public function __clone()
    {
        $this->id = clone new YaMarketProductsCardEventUid();
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }

    public function getId(): YaMarketProductsCardEventUid
    {
        return $this->id;
    }

    /**
     * Идентификатор YaMarketProductsCard
     */
    public function setMain(YaMarketProductsCardUid|YaMarketProductsCard $main): void
    {
        $this->main = $main instanceof YaMarketProductsCard ? $main->getId() : $main;
    }


    public function getMain(): ?YaMarketProductsCardUid
    {
        return $this->main;
    }

    public function getDto($dto): mixed
    {
        if($dto instanceof YaMarketProductsCardEventInterface)
        {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    public function setEntity($dto): mixed
    {
        if($dto instanceof YaMarketProductsCardEventInterface)
        {
            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }


    //	public function isModifyActionEquals(ModifyActionEnum $action) : bool
    //	{
    //		return $this->modify->equals($action);
    //	}

    //	public function getUploadClass() : YaMarketProductsCardImage
    //	{
    //		return $this->image ?: $this->image = new YaMarketProductsCardImage($this);
    //	}

    //	public function getNameByLocale(Locale $locale) : ?string
    //	{
    //		$name = null;
    //
    //		/** @var YaMarketProductsCardTrans $trans */
    //		foreach($this->translate as $trans)
    //		{
    //			if($name = $trans->name($locale))
    //			{
    //				break;
    //			}
    //		}
    //
    //		return $name;
    //	}
}
