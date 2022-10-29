<?php

namespace App\Models\Payments;

use App\Enums\Payment\OnlinePaymentStatusEnum;
use App\Enums\Payment\OnlinePaymentMethodEnum;

use App\Models\Orders\Order;
use App\Models\Payments\OnlinePaymentStatus;

use Encore\Admin\Facades\Admin;
use Encore\Admin\Auth\Database\Administrator;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\{Model, Relations};

class OnlinePayment extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $appends = [
        'link',
    ];

    public static function boot()
    {
        parent::boot();
        self::created(function ($model) {
            $model->statuses()->create([
                'admin_user_id'          => Admin::user()->id ?? null,
                'payment_status_enum_id' => OnlinePaymentStatusEnum::PENDING,
            ]);
        });
    }

    /**
     * Payment statuses
     *
     * @return Relations\HasMany
     */
    public function statuses(): Relations\HasMany
    {
        return $this->hasMany(OnlinePaymentStatus::class);
    }

    /**
     * Get the order that owns the payment.
     */
    public function order(): Relations\BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the order that owns the payment.
     */
    public function admin(): Relations\BelongsTo
    {
        return $this->belongsTo(Administrator::class, 'admin_user_id');
    }

    /**
     * Get online paymeny link.
     *
     * @return string
     */
    public function getLinkAttribute(): ?string
    {
        $enum = OnlinePaymentMethodEnum::enumByValue($this->method_enum_id);
        if ($enum) {
            return match ($enum) {
                OnlinePaymentMethodEnum::ERIP => route('pay.erip', $this->payment_url, true),
            };
        }
        return null;
    }
}
