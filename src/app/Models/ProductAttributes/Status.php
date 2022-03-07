<?php

namespace App\Models\ProductAttributes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Status extends Model
{
    use HasFactory;

    /**
     * @param Builder $builder
     * @param array $values
     * @return Builder
     */
    public static function applyFilter(Builder $builder, array $values)
    {
        foreach ($values as $slug => $urlModel) {
            switch ($slug) {
                case 'st-new':
                    $builder->where('old_price', 0);
                    break;

                case 'st-sale':
                    $builder->whereColumn('price', '<', 'old_price');
                    break;
            }
        }

        return $builder;
    }
}
