<?php

namespace App\Models\Logs;

use App\Models\Orders\Order;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class SmsLog
 *
 * @property int $id
 * @property int|null $admin_id
 * @property int|null $order_id
 * @property string $route
 * @property string $phone
 * @property string $text
 * @property string $status
 * @property \Carbon\Carbon $created_at
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
        'order_id',
        'route',
        'phone',
        'text',
        'status',
        'created_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime:d.m.Y H:i:s',
    ];

    /**
     * The name of the "updated at" column.
     *
     * @var string
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
     * The order to which the message refers
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
