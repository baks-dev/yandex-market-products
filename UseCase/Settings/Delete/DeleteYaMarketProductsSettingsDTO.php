<?php
/*
 *  Copyright 2022.  Baks.dev <admin@baks.dev>
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *   limitations under the License.
 *
 */

namespace BaksDev\Yandex\Market\Products\UseCase\Settings\Delete;


use BaksDev\Yandex\Market\Products\Entity\Settings\Event\YaMarketProductsSettingsEventInterface;
use BaksDev\Yandex\Market\Products\Type\Settings\Event\YaMarketProductsSettingsEventUid;
use Symfony\Component\Validator\Constraints as Assert;

/** @see YaMarketProductsSettingsEvent */
final class DeleteYaMarketProductsSettingsDTO implements YaMarketProductsSettingsEventInterface
{
    /**
     * Идентификатор события
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    private ?YaMarketProductsSettingsEventUid $id = null;

    /**
     * Модификатор
     */
    #[Assert\Valid]
    private Modify\ModifyDTO $modify;

    public function __construct()
    {
        $this->modify = new Modify\ModifyDTO();
    }


    /**
     * Идентификатор события
     */
    public function getEvent(): ?YaMarketProductsSettingsEventUid
    {
        return $this->id;
    }

    public function setId(YaMarketProductsSettingsEventUid $id): void
    {
        $this->id = $id;
    }


    /**
     * Модификатор
     */
    public function getModify(): Modify\ModifyDTO
    {
        return $this->modify;
    }


}