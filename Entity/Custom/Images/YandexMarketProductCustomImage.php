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

namespace BaksDev\Yandex\Market\Products\Entity\Custom\Images;

use BaksDev\Core\Entity\EntityState;
use BaksDev\Files\Resources\Upload\UploadEntityInterface;
use BaksDev\Yandex\Market\Products\Entity\Custom\YandexMarketProductCustom;
use BaksDev\Yandex\Market\Products\Type\Image\YandexMarketProductImageUid;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'ya_market_product_custom_images')]
#[ORM\Index(columns: ['root'])]
class YandexMarketProductCustomImage extends EntityState implements UploadEntityInterface
{
    /** ID */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\Column(type: YandexMarketProductImageUid::TYPE)]
    private readonly YandexMarketProductImageUid $id;

    /** Идентификатор продукта Яндекс Маркет */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\ManyToOne(targetEntity: YandexMarketProductCustom::class, inversedBy: 'images')]
    #[ORM\JoinColumn(name: 'invariable', referencedColumnName: 'invariable')]
    private YandexMarketProductCustom $invariable;

    /** Название файла */
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    #[ORM\Column(type: Types::STRING)]
    private string $name;

    /** Расширение файла */
    #[Assert\NotBlank]
    #[Assert\Choice(['png', 'gif', 'jpg', 'jpeg', 'webp'])]
    #[ORM\Column(type: Types::STRING)]
    private string $ext;

    /** Размер файла */
    #[Assert\NotBlank]
    #[Assert\Range(max: 10485760)] // (1024 * 1024) *10
    #[ORM\Column(type: Types::INTEGER)]
    private int $size = 0;

    /** Файл загружен на CDN */
    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $cdn = false;

    /** Заглавное фото */
    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $root = false;

    public function __construct(YandexMarketProductCustom $yandexMarket)
    {
        $this->id = clone(new YandexMarketProductImageUid());
        $this->invariable = $yandexMarket;
    }

    public function getId(): YandexMarketProductImageUid
    {
        return $this->id;
    }


    public function updFile(string $name, string $ext, int $size): void
    {
        $this->name = $name;
        $this->ext = $ext;
        $this->size = $size;
        $this->cdn = false;
    }

    public function updCdn(?string $ext = null): void
    {
        if($ext)
        {
            $this->ext = $ext;
        }

        $this->cdn = true;
    }

    public function getExt(): string
    {
        return $this->ext;
    }
}