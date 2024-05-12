<?php

namespace App\Models\Promo;

use App\Enums\Promo\SaleAlgorithm;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $title
 * @property string|null $label_text
 * @property \Illuminate\Support\Carbon $start_datetime
 * @property \Illuminate\Support\Carbon $end_datetime
 * @property float|null $sale_percentage discount amount in percentage
 * @property float|null $sale_fix fixed discount amount
 * @property \App\Enums\Promo\SaleAlgorithm $algorithm
 * @property array|null $categories
 * @property array|null $collections
 * @property array|null $styles
 * @property array|null $seasons
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
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Promo\Promocode[] $promocodes
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Promo\Sale actual()
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Sale extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>|bool
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be cast.
     *
     * @var array
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
     * Get the promocodes associated with the sale.
     */
    public function promocodes(): HasMany
    {
        return $this->hasMany(Promocode::class);
    }

    /**
     * Encode the given value as JSON.
     */
    protected function asJson(mixed $value): string|false|null
    {
        if (!$value) {
            return null;
        }

        return json_encode(array_map('intval', $value));
    }
}
