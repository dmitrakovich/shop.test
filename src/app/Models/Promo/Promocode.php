<?php

namespace App\Models\Promo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $sale_id
 * @property string $code
 * @property string|null $description
 * @property int|null $timer_sec
 * @property bool|null $activations_count
 * @property string|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\Promo\Sale|null $sale
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Promocode extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>|bool
     */
    protected $guarded = ['id'];

    /**
     * Get the sale that owns the promocode.
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }
}
