<?php

namespace App\Contracts;

interface AuthorInterface
{
    /**
     * Get the user's full name.
     */
    public function getFullName(): string;

    /**
     * Get the user's type name.
     */
    public static function getTypeName(): string;
}
