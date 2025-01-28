<?php

namespace App\Models\Logs;

use App\Admin\Models\Administrator;
use App\Models\Ads\Mailing;
use App\Models\Orders\Order;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $admin_id
 * @property int|null $user_id
 * @property int|null $order_id
 * @property int|null $mailing_id
 * @property string $route
 * @property string $phone
 * @property string $text
 * @property string|null $status
 * @property \Illuminate\Support\Carbon|null $created_at
 *
 * @property-read \App\Admin\Models\Administrator|null $admin
 * @property-read \App\Models\User\User|null $user
 * @property-read \App\Models\Orders\Order|null $order
 * @property-read \App\Models\Ads\Mailing|null $mailing
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class SmsLog extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'log_sms';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'admin_id',
        'user_id',
        'order_id',
        'mailing_id',
        'route',
        'phone',
        'text',
        'status',
        'created_at',
    ];

    /**
     * The name of the "updated at" column.
     *
     * @var string|null
     */
    public const UPDATED_AT = null;

    /**
     * The manager who sent the message
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Administrator::class);
    }

    /**
     * The user to which the message refers
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The order to which the message refers
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * The mailing to which the message refers
     */
    public function mailing(): BelongsTo
    {
        return $this->belongsTo(Mailing::class);
    }

    /**
     * Prepare a date for array / JSON serialization.
     */
    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format('d.m.Y H:i:s');
    }
}
