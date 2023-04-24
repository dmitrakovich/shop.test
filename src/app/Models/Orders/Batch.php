<?php

namespace App\Models\Orders;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;

class Batch extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Orders
     */
    public function orders(): Relations\HasMany
    {
        return $this->hasMany(Order::class);
    }
}
