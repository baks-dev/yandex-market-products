<?php

namespace BaksDev\Yandex\Market\Products\Type\Settings\Property;

use BaksDev\Yandex\Market\Products\Type\Settings\Property\Properties\Collection\YaMarketProductPropertyInterface;
use InvalidArgumentException;

final class YaMarketProductProperty
{
    public const TYPE = 'ya_market_product_property';

    private ?YaMarketProductPropertyInterface $property = null;


    public function __construct(YaMarketProductPropertyInterface|self|string $property)
    {

        if(is_string($property) && class_exists($property))
        {
            $instance = new $property();

            if($instance instanceof YaMarketProductPropertyInterface)
            {
                $this->property = $instance;
                return;
            }
        }

        if($property instanceof YaMarketProductPropertyInterface)
        {
            $this->property = $property;
            return;
        }

        if($property instanceof self)
        {
            $this->property = $property->getYaMarketProductProperty();
            return;
        }

        /** @var YaMarketProductPropertyInterface $declare */
        foreach(self::getDeclared() as $declare)
        {

            if($declare::equals($property))
            {
                $this->property = new $declare;
                return;
            }
        }

        //throw new InvalidArgumentException(sprintf('Invalid argument %s', $property));

    }


    public function __toString(): string
    {
        return $this->property ? $this->property->getvalue() : '';
    }

    public function getYaMarketProductProperty(): ?YaMarketProductPropertyInterface
    {
        return $this->property;
    }

    public function getYaMarketProductPropertyValue(): string
    {
        return $this->property->getValue();
    }


    public static function cases(): array
    {
        $case = [];

        foreach(self::getDeclared() as $property)
        {
            /** @var YaMarketProductPropertyInterface $property */
            $class = new $property;
            $case[$class::priority()] = new self($class);
        }

        return $case;
    }

    public static function getDeclared(): array
    {
        return array_filter(
            get_declared_classes(),
            static function($className) {
                return in_array(YaMarketProductPropertyInterface::class, class_implements($className), true);
            }
        );
    }

    public function equals(mixed $property): bool
    {
        $property = new self($property);

        return $this->getYaMarketProductPropertyValue() === $property->getYaMarketProductPropertyValue();
    }


}