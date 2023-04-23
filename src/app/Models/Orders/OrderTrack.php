<?php

namespace App\Models\Orders;

use App\Enums\DeliveryTypeEnum;
use App\Models\Orders\Order;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;

class OrderTrack extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    /**
     * The attributes that should be cast.
     * @var array
     */
    protected $casts = [
        'delivery_type_enum' => DeliveryTypeEnum::class,
    ];

    /**
     * Order.
     */
    public function order(): Relations\BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
