<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductGroup extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    /**
     * Product relation.
     */
    public function products()
    {
      return $this->hasMany(Product::class, 'id', 'product_group_id');
    }

}
