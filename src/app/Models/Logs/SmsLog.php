<?php

namespace App\Models\Logs;

use App\Admin\Models\Administrator;
use App\Casts\AsSmsDeliveryStatus;
use App\Enums\Sms\SmsDeliveryChannel;
use App\Enums\Sms\SmsDeliveryStatus;
use App\Enums\Sms\SmsRoute;
use App\Models\Ads\Mailing;
use App\Models\Orders\Order;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $admin_id
 * @property int|null $user_id
 * @property int|null $order_id
 * @property int|null $mailing_id
 * @property SmsRoute $route
 * @property string $phone
 * @property string $text
 * @property string|null $sms_id
 * @property SmsDeliveryStatus|string|null $status
 * @property SmsDeliveryChannel|null $delivery_channel
 * @property \Illuminate\Support\Carbon|null $delivered_at
 * @property \Illuminate\Support\Carbon|null $read_at
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
        'sms_id',
        'text',
        'status',
        'delivery_channel',
        'delivered_at',
        'read_at',
        'created_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'route' => SmsRoute::class,
            'status' => AsSmsDeliveryStatus::class,
            'delivery_channel' => SmsDeliveryChannel::class,
            'delivered_at' => 'datetime',
            'read_at' => 'datetime',
        ];
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopePendingDeliveryStatusUpdate(Builder $query): Builder
    {
        return $query
            ->whereNotNull('sms_id')
            ->where('created_at', '>=', now()->subDays(2))
            ->where(function (Builder $query): void {
                $query->whereNull('status')
                    ->orWhereIn('status', SmsDeliveryStatus::trackableValues())
                    ->orWhere(function (Builder $query): void {
                        $query->where('delivery_channel', SmsDeliveryChannel::Viber->value)
                            ->whereIn('status', SmsDeliveryStatus::deliveredValues());
                    });
            });
    }

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
