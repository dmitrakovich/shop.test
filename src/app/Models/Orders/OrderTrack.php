<?php

namespace App\Models\Orders;

use App\Enums\DeliveryTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;

/**
 * @property int $id
 * @property string|null $track_number Трек номер заказа
 * @property string|null $track_link Ссылка для отслеживания трек номера
 * @property int|null $order_id Номер заказа
 * @property \App\Enums\DeliveryTypeEnum $delivery_type_enum Тип доставки
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\Orders\Order|null $order
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class OrderTrack extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * The attributes that should be cast.
     *
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
