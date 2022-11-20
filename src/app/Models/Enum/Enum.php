<?php

namespace App\Models\Enum;

/**
 * Class Enum
 */
interface Enum
{
    /**
     * @return array
     */
    public static function getKeys(): array;

    /**
     * @return array
     */
    public static function getValues(): array;

    /**
     * @return mixed
     */
    public static function getDefaultValue();
}
