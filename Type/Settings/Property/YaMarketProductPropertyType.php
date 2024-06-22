<?php

namespace BaksDev\Yandex\Market\Products\Type\Settings\Property;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Types\Type;
use InvalidArgumentException;

final class YaMarketProductPropertyType extends Type
{
    public function convertToDatabaseValue($value, AbstractPlatform $platform): string
    {
        return (string) $value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?YaMarketProductProperty
    {
        return !empty($value) ? new YaMarketProductProperty($value) : null;
    }

    public function getName(): string
    {
        return YaMarketProductProperty::TYPE;
    }


    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL($column);
    }
}
