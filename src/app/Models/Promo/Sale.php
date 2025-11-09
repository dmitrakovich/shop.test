<?php

namespace App\Models\Promo;

use App\Enums\Promo\SaleAlgorithm;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $title
 * @property string|null $label_text
 * @property \Illuminate\Support\Carbon $start_datetime
 * @property \Illuminate\Support\Carbon $end_datetime
 * @property \App\Enums\Promo\SaleAlgorithm $algorithm
 * @property string|null $sale_percentage discounts amount in percentage
 * @property int|null $sale_fix fixed discount amount
 * @property array<int, int>|null $categories
 * @property array<int, int>|null $collections
 * @property array<int, int>|null $styles
 * @property array<int, int>|null $seasons
 * @property bool $only_new
 * @property bool $only_discount
 * @property bool $add_client_sale
 * @property bool $add_review_sale
 * @property bool $has_installment
 * @property bool $has_cod is available cash on delivery payment method
 * @property bool $has_fitting
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Promo\SaleSetting[] $settings
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Promo\Promocode[] $promocodes
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Promo\Sale actual()
 */
class Sale extends Model
{
    use SoftDeletes;

    /**
     * Indicates if all mass assignment is enabled.
     *
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'algorithm' => SaleAlgorithm::class,
        'categories' => 'array',
        'collections' => 'array',
        'styles' => 'array',
        'seasons' => 'array',
        'only_new' => 'boolean',
        'only_discount' => 'boolean',
        'add_client_sale' => 'boolean',
        'add_review_sale' => 'boolean',
        'has_installment' => 'boolean',
        'has_cod' => 'boolean',
        'has_fitting' => 'boolean',
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
    ];

    /**
     * Scope a query to only include actual sales
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeActual(Builder $query): Builder
    {
        return $query
            ->where(function ($query) {
                return $query->where('start_datetime', '<', now())
                    ->orWhereNull('start_datetime');
            })
            ->where(function ($query) {
                return $query->where('end_datetime', '>=', now())
                    ->orWhereNull('end_datetime');
            })
            ->whereDoesntHave('promocodes');
    }

    /**
     * @return HasMany<SaleSetting, $this>
     */
    public function settings(): HasMany
    {
        return $this->hasMany(SaleSetting::class);
    }

    /**
     * Get the promocodes associated with the sale.
     *
     * @return HasMany<Promocode, $this>
     */
    public function promocodes(): HasMany
    {
        return $this->hasMany(Promocode::class);
    }

    /**
     * Encode the given value as JSON.
     *
     * @param  int  $flags
     */
    protected function asJson(mixed $value, $flags = 0): string|false|null
    {
        if (!$value) {
            return null;
        }

        return json_encode(array_map('intval', $value));
    }
}
