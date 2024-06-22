<?php

namespace BaksDev\Yandex\Market\Products\Type\Settings\Event;

use App\Kernel;
use BaksDev\Core\Type\UidType\Uid;
use Symfony\Component\Uid\AbstractUid;

final class YaMarketProductsSettingsEventUid extends Uid
{
    public const TEST = 'eecc6715-beef-7ddf-be18-f2cc812bee29';

    public const TYPE = 'ya_market_settings_event';

}
