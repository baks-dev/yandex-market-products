<?php
/*
 *  Copyright 2023.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Yandex\Market\Products\Entity\Settings;

use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Yandex\Market\Products\Entity\Settings\Event\YaMarketProductsSettingsEvent;
use BaksDev\Yandex\Market\Products\Type\Settings\Event\YaMarketProductsSettingsEventUid;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'ya_market_products_settings')]
class YaMarketProductsSettings
{
    /** ID */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\Column(type: CategoryProductUid::TYPE)]
    private CategoryProductUid $id;

    /** ID События */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Column(type: YaMarketProductsSettingsEventUid::TYPE, unique: true)]
    private YaMarketProductsSettingsEventUid $event;


    public function __toString(): string
    {
        return (string) $this->id;
    }

    public function getId(): CategoryProductUid
    {
        return $this->id;
    }


    public function getEvent(): YaMarketProductsSettingsEventUid
    {
        return $this->event;
    }

    public function setEvent(YaMarketProductsSettingsEvent $event): void
    {
        $this->event = $event->getId();
        $this->id = $event->getSettings();
    }

}