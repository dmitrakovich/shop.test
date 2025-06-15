<?php

namespace App\Casts;

use App\ValueObjects\Phone;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class AsPhone implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): Phone
    {
        return new Phone($value);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): int
    {
        if ($value instanceof Phone) {
            return $value->forSave();
        }

        return Phone::fromRawString((string)$value)->forSave();
    }
}
