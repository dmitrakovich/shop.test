<?php

namespace App\Models\Enum;

/**
 * Class Enum
 * @package App\Models\Enum
 */
abstract class EnumAbstract
{
    /**
     * @return array
     */
    static function getKeys(): array
    {
        $class = new \ReflectionClass(get_called_class());
        return array_keys($class->getConstants());
    }

    /**
     * @return array
     */
    static function getValues(): array
    {
        $class = new \ReflectionClass(get_called_class());
        return array_values($class->getConstants());
    }

}
