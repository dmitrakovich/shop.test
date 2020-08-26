<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
/**
 * Class Product
 * 
 * @package App\Models
 * 
 * @property \App\Category      $category
 * @property string             $title
 * @property string             $slug
 * ...
 */
class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        '...',
    ];
    //
    /**
     * Категория товара
     *
     * @return Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
