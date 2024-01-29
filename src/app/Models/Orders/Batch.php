<?php

namespace App\Models\Orders;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;

/**
 * @property int $id
 * @property string|null $dispatch_date Дата отправки
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Orders\Order[] $orders
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
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
