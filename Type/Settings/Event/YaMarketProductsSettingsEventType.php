<?php

namespace BaksDev\Yandex\Market\Products\Type\Settings\Event;


use BaksDev\Core\Type\UidType\UidType;

final class YaMarketProductsSettingsEventType extends UidType
{

    public function getClassType(): string
    {
        return YaMarketProductsSettingsEventUid::class;
    }
    
    public function getName(): string
    {
        return YaMarketProductsSettingsEventUid::TYPE;
    }
}