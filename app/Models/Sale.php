<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sale extends Model
{
    use HasFactory;
    use SoftDeletes;
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
}
