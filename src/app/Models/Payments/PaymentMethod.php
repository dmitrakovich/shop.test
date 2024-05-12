<?php

namespace Payments;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $instance
 * @property bool $active
 * @property int $sorting
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\Payments\PaymentMethod active()
 * @method static \Illuminate\Database\Eloquent\Builder|\Payments\PaymentMethod filterInstallment(bool $availableInstallment)
 * @method static \Illuminate\Database\Eloquent\Builder|\Payments\PaymentMethod filterCOD(bool $availableCOD)
 * @method static \Illuminate\Database\Eloquent\Builder|\Payments\PaymentMethod filterByCountry(string $countryCode)
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class PaymentMethod extends Model
{
    /**
     * Scope a query to only include active payment methods.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('active', true);
    }

    /**
     * Scope a query to filter by installment availability.
     */
    public function scopeFilterInstallment(Builder $query, bool $availableInstallment): void
    {
        if (!$availableInstallment) {
            $query->where('instance', '!=', 'Installment');
        }
    }

    /**
     * Scope a query to filter by cash on delivery (COD) availability.
     */
    public function scopeFilterCOD(Builder $query, bool $availableCOD): void
    {
        if (!$availableCOD) {
            $query->where('instance', '!=', 'COD');
        }
    }

    /**
     * Scope a query to filter payment methods by country code.
     */
    public function scopeFilterByCountry(Builder $query, string $countryCode): void
    {
        $query->whereIn('instance', match ($countryCode) {
            'BY' => ['COD', 'Card', 'ERIP', 'Installment'],
            'RU' => ['COD', 'OnlinePayment'],
            'KZ' => ['COD', 'Card'],
            default => ['Card'],
        });
    }
}
