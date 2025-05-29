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

namespace BaksDev\Yandex\Market\Products\UseCase\NewEdit;

use BaksDev\Products\Product\Entity\ProductInvariable;
use BaksDev\Products\Product\Type\Invariable\ProductInvariableUid;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Yandex\Market\Products\Entity\Custom\YandexMarketProductCustom;
use BaksDev\Yandex\Market\Products\Entity\Custom\YandexMarketProductCustomInterface;
use BaksDev\Yandex\Market\Products\UseCase\NewEdit\Images\YandexMarketProductCustomImagesDTO;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/** @see YandexMarketProductCustom */
final class YandexMarketCustomProductDTO implements YandexMarketProductCustomInterface
{
    /**
     * Invariable продукта
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    private ProductInvariableUid $invariable;

    /**
     * Коллекция изображений продукта для ЯндексМаркета
     *
     * @var ArrayCollection<int, YandexMarketProductCustomImagesDTO> $images
     */
    #[Assert\Valid]
    private ArrayCollection $images;

    public function __construct()
    {
        $this->images = new ArrayCollection();
    }

    /**
     * ProductInvariable
     */

    public function getInvariable(): ProductInvariableUid
    {
        return $this->invariable;
    }

    public function setInvariable(ProductInvariableUid|ProductInvariable|string $invariable): self
    {
        if(is_string($invariable))
        {
            $invariable = new ProductInvariableUid($invariable);
        }

        if($invariable instanceof ProductInvariable)
        {
            $invariable = $invariable->getId();
        }

        $this->invariable = $invariable;

        return $this;
    }

    /**
     * @return ArrayCollection<int, YandexMarketProductCustomImagesDTO>
     */
    public function getImages(): ArrayCollection
    {
        return $this->images;
    }

    public function addImage(YandexMarketProductCustomImagesDTO $image): void
    {
        /** Пропускаем, если форма не содержит изображения и изображение изображению не присвоено имя */
        if(null === $image->getFile() && null === $image->getName())
        {
            return;
        }

        /** Пропускаем, если форма не содержит изображения, либо изображение уже есть в коллекции */
        $filter = $this->images->filter(function(YandexMarketProductCustomImagesDTO $current) use ($image) {

            if(null !== $image->getFile())
            {
                return false;
            }

            return $image->getName() === $current->getName();
        });

        if($filter->isEmpty())
        {
            $this->images->add($image);
        }
    }

    public function removeImage(YandexMarketProductCustomImagesDTO $image): void
    {
        $this->images->removeElement($image);
    }
}