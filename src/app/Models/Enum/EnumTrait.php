<?php

namespace App\Models\Enum;

/**
 * Class Enum
 */
trait EnumTrait
{
    /**
     * @return array
     */
    public static function getKeys(): array
    {
        $class = new \ReflectionClass(get_called_class());

        return array_keys($class->getConstants());
    }

    /**
     * @return array
     */
    public static function getValues(): array
    {
        $class = new \ReflectionClass(get_called_class());

        return array_values($class->getConstants());
    }

    /**
     * @return mixed
     */
    public static function getDefaultValue()
    {
        $class = new \ReflectionClass(get_called_class());
        $values = $class->getConstants();

        return reset($values);
    }
}
