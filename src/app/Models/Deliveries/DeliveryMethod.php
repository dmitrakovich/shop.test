<?php

namespace Deliveries;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property \Deliveries\AbstractDeliveryMethod $instance
 * @property bool $active
 * @property int $sorting
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\Deliveries\DeliveryMethod active()
 * @method static \Illuminate\Database\Eloquent\Builder|\Deliveries\DeliveryMethod filterFitting(bool $availableFitting)
 * @method static \Illuminate\Database\Eloquent\Builder|\Deliveries\DeliveryMethod filterByCountry(string $countryCode)
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class DeliveryMethod extends Model
{
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'instance' => DeliveryInstanceCast::class,
        'active' => 'boolean',
    ];

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
            'BY' => ['BelpostCourierFitting', 'BelpostCourier', 'Belpost', 'BelpostEMS', 'ShopPvz'],
            'RU' => ['BelpostEMS', 'SdekPvz'],
            'KZ' => ['SdekPvz'],
            default => ['BelpostEMS'],
        });
    }
}
