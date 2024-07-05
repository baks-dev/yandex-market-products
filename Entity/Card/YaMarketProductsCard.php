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

namespace BaksDev\Yandex\Market\Products\Entity\Card;

use BaksDev\Yandex\Market\Products\Entity\Card\Event\YaMarketProductsCardEvent;
use BaksDev\Yandex\Market\Products\Type\Card\Event\YaMarketProductsCardEventUid;
use BaksDev\Yandex\Market\Products\Type\Card\Id\YaMarketProductsCardUid;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/* YaMarketProductsCard */

#[ORM\Entity]
#[ORM\Table(name: 'ya_market_products_card')]
class YaMarketProductsCard
{
    /**
     * Идентификатор сущности
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\Column(type: YaMarketProductsCardUid::TYPE)]
    private YaMarketProductsCardUid $id;

    /**
     * Идентификатор События
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Column(type: YaMarketProductsCardEventUid::TYPE, unique: true)]
    private YaMarketProductsCardEventUid $event;

    public function __construct()
    {
        $this->id = new YaMarketProductsCardUid();
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }

    /**
     * Идентификатор
     */
    public function getId(): YaMarketProductsCardUid
    {
        return $this->id;
    }

    /**
     * Идентификатор События
     */
    public function getEvent(): YaMarketProductsCardEventUid
    {
        return $this->event;
    }

    public function setEvent(YaMarketProductsCardEventUid|YaMarketProductsCardEvent $event): void
    {
        $this->event = $event instanceof YaMarketProductsCardEvent ? $event->getId() : $event;
    }
}
