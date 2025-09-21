<?php

namespace App\Models\User;

use App\Enums\User\OrderType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;

/**
 * @property int $id
 * @property int $user_id
 * @property \App\Enums\User\OrderType|null $last_order_type Тип последнего заказа
 * @property \Illuminate\Support\Carbon|null $last_order_date Дата последнего заказа
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\User\User|null $user
 */
class UserMetadata extends Model
{
    /**
     * Indicates if all mass assignment is enabled.
     *
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'last_order_type' => OrderType::class,
        'last_order_date' => 'datetime',
    ];

    /**
     * Define a relationship with the User model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
