<?php

namespace Payments;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * PaymentMethod class
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
class PaymentMethod extends Model
{
    use HasFactory;

    /**
     * Scope a query to only include active payment method.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('active', true);
    }

    /**
     * Scope a query to filter payment methods with installment if needed.
     */
    public function scopeFilterInstallment(Builder $query, bool $availableInstallment): void
    {
        if (!$availableInstallment) {
            $query->where('instance', '!=', 'Installment');
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
