<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartData extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'cart_id',
        'product_id',
        'size_id',
        'color_id',
        'count',
    ];
}
