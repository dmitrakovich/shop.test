<?php

namespace Deliveries;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class DeliveryInstanceCast implements CastsAttributes
{
    /**
     * Cast the given value.
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): AbstractDeliveryMethod
    {
        $instance = "Deliveries\\$value";
        if (class_exists($instance)) {
            return new $instance($model);
        }

        return new AbstractDeliveryMethod($model);
    }

    /**
     * Prepare the given value for storage.
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        if ($value instanceof AbstractDeliveryMethod) {
            return class_basename($value);
        }

        return (string)$value;
    }
}
