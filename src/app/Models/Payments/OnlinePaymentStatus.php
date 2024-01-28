<?php

namespace App\Models\Payments;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $online_payment_id ID платежа
 * @property int|null $admin_user_id ID admin пользователя
 * @property bool|null $payment_status_enum_id enum ID статуса плажета
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class OnlinePaymentStatus extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
}
