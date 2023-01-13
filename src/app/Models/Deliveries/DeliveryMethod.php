<?php

namespace Deliveries;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * DeliveryMethod class
 *
 * @property int $id
 * @property string $name
 * @property string $instance
 * @property bool $active
 * @property int $sorting
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @method static Builder active() Only include active delivery method
 *
 * @mixin Builder
 */
class DeliveryMethod extends Model
{
    use HasFactory;

    /**
     * Scope a query to only include active delivery method.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('active', true);
    }

    /**
     * Scope a query to filter delivery methods with fitting if needed.
     */
    public function scopeFilterFitting(Builder $query, bool $availableFitting): void
    {
        if (!$availableFitting) {
            $query->where('instance', '!=', 'BelpostCourierFitting');
        }
    }

    /**
     * Scope a query to filter delivery methods by country code.
     */
    public function scopeFilterByCountry(Builder $query, string $countryCode): void
    {
        $query->whereIn('instance', match ($countryCode) {
            'BY' => ['BelpostCourierFitting', 'BelpostCourier', 'Belpost', 'BelpostEMS'],
            'RU' => ['BelpostEMS', 'SdekPvz'],
            'KZ' => ['SdekPvz'],
            default => ['BelpostEMS'],
        });
    }
}
