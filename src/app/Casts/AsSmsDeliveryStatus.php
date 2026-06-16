<?php

namespace App\Casts;

use App\Enums\Sms\SmsDeliveryStatus;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/** @implements CastsAttributes<SmsDeliveryStatus|string|null, SmsDeliveryStatus|string|null> */
class AsSmsDeliveryStatus implements CastsAttributes
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): SmsDeliveryStatus|string|null
    {
        if ($value === null) {
            return null;
        }

        return SmsDeliveryStatus::resolve((string)$value);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof SmsDeliveryStatus) {
            return $value->value;
        }

        return (string)$value;
    }
}
