<?php

namespace App\Models\Payments;

use App\Models\Orders\OrderItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Installment class
 *
 * @property integer $id
 * @property integer $order_item_id
 * @property string $contract_number
 * @property float $monthly_fee
 * @property boolean $send_notifications
 * @property Carbon|null $notice_sent_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Installment extends Model
{
    use HasFactory;

    const PAYMENT_METHOD_ID = 4;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'send_notifications' => 'boolean',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'notice_sent_at',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'contract_number',
        'monthly_fee',
        'send_notifications',
        'notice_sent_at',
    ];

    /**
     * Get the order that owns the installment.
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }
}
