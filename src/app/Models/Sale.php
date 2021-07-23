<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Sale model
 *
 * @property int $id
 * @property string $title
 * @property string $label_text
 * @property \Illuminate\Support\Carbon $start_datetime
 * @property \Illuminate\Support\Carbon $end_datetime
 * @property string $algorithm
 * @property string $sale
 * @property array|null $categories
 * @property array|null $collections
 * @property array|null $styles
 * @property array|null $seasons
 * @property boolean $only_new
 * @property boolean $add_client_sale
 * @property boolean $has_installment
 * @property boolean $has_fitting
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon $deleted_at
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
    ];

    protected $dates = [
        'start_datetime',
        'end_datetime',
    ];

    /**
     * Выбор актуальных
     *
     * @param Builder $query
     * @param string $search
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
     * @param \DateTimeInterface $date
     * @return string
     */
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('d.m.Y H:i:s');
    }
}
