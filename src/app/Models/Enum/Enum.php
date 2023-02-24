<?php

namespace App\Models\Enum;

/**
 * Class Enum
 */
interface Enum
{
    public static function getKeys(): array;

    public static function getValues(): array;

    /**
     * @return mixed
     */
    public static function getDefaultValue();
}
