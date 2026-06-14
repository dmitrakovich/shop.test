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
 * @property string|null $sms_id
 * @property string $text
 * @property string|null $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Admin\Models\Administrator|null $admin
 * @property-read \App\Models\User\User|null $user
 * @property-read \App\Models\Orders\Order|null $order
 * @property-read \App\Models\Ads\Mailing|null $mailing
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
        'sms_id',
        'status',
        'created_at',
    ];

    /**
     * The manager who sent the message
     *
     * @return BelongsTo<Administrator, $this>
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Administrator::class);
    }

    /**
     * The user to which the message refers
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The order to which the message refers
     *
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * The mailing to which the message refers
     *
     * @return BelongsTo<Mailing, $this>
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
