<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $title
 * @property string|null $label_text
 * @property \Illuminate\Support\Carbon $start_datetime
 * @property \Illuminate\Support\Carbon $end_datetime
 * @property string $algorithm
 * @property string $sale
 * @property array|null $categories
 * @property array|null $collections
 * @property array|null $styles
 * @property array|null $seasons
 * @property bool $only_new
 * @property bool $only_discount
 * @property bool $add_client_sale
 * @property bool $add_review_sale
 * @property bool $has_installment
 * @property bool $has_fitting
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Sale actual()
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Sale extends Model
{
    use HasFactory;
    use SoftDeletes;

    const ALGORITHM_FAKE = 'fake';

    const ALGORITHM_SIMPLE = 'simple';

    const ALGORITHM_COUNT = 'count';

    const ALGORITHM_ASCENDING = 'ascending';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'categories' => 'array',
        'collections' => 'array',
        'styles' => 'array',
        'seasons' => 'array',
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
    ];

    /**
     * Выбор актуальных
     *
     * @param  string  $search
     * @return Builder
     */
    public function scopeActual(Builder $query)
    {
        return $query->where(function ($query) {
            return $query->where('start_datetime', '<', now())
                ->orWhereNull('start_datetime');
        })
            ->where(function ($query) {
                return $query->where('end_datetime', '>=', now())
                    ->orWhereNull('end_datetime');
            });
    }

    /**
     * Farmat date
     *
     * @return string
     */
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('d.m.Y H:i:s');
    }
}
