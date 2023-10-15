<?php

namespace App\Models\Payments;

use App\Enums\Payment\OnlinePaymentMethodEnum;
use App\Enums\Payment\OnlinePaymentStatusEnum;
use App\Models\Orders\Order;
use App\Admin\Models\Administrator;
use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;

class OnlinePayment extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $appends = [
        'link',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'method_enum_id' => OnlinePaymentMethodEnum::class,
        'last_status_enum_id' => OnlinePaymentStatusEnum::class,
        'request_data' => 'json',
    ];

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->last_status_enum_id = match ($model->method_enum_id) {
                OnlinePaymentMethodEnum::COD => OnlinePaymentStatusEnum::SUCCEEDED,
                default => OnlinePaymentStatusEnum::PENDING
            };
        });
        self::created(function ($model) {
            if ($model->last_status_enum_id === OnlinePaymentStatusEnum::PENDING) {
                $model->statuses()->create([
                    'admin_user_id' => Admin::user()->id ?? null,
                    'payment_status_enum_id' => OnlinePaymentStatusEnum::PENDING,
                ]);
            }
        });
    }

    /**
     * Payment statuses
     */
    public function statuses(): Relations\HasMany
    {
        return $this->hasMany(OnlinePaymentStatus::class);
    }

    /**
     * Payment statuses
     */
    public function lastStatus(): Relations\HasOne
    {
        return $this->hasOne(OnlinePaymentStatus::class)->latest('id');
    }

    /**
     * Last canceled status
     */
    public function lastCanceledStatus(): Relations\HasOne
    {
        return $this->lastStatus()->where('payment_status_enum_id', OnlinePaymentStatusEnum::CANCELED);
    }

    /**
     * Last succeeded status
     */
    public function lastSucceededStatus(): Relations\HasOne
    {
        return $this->lastStatus()->where('payment_status_enum_id', OnlinePaymentStatusEnum::SUCCEEDED);
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
     */
    public function getLinkAttribute(): ?string
    {
        if ($this->method_enum_id) {
            return match ($this->method_enum_id) {
                OnlinePaymentMethodEnum::ERIP => isset($this->payment_url) ? route('pay.erip', $this->payment_url, true) : null,
                OnlinePaymentMethodEnum::YANDEX => isset($this->link_code) ? route('pay.yandex', $this->link_code, true) : null,
                default => null
            };
        }

        return null;
    }

    /**
     * Is can cancel the payment?
     */
    public function canCancelPayment(): bool
    {
        return $this->last_status_enum_id === OnlinePaymentStatusEnum::WAITING_FOR_CAPTURE;
    }

    /**
     * Is can capture the payment?
     */
    public function canCapturePayment(): bool
    {
        return $this->last_status_enum_id === OnlinePaymentStatusEnum::WAITING_FOR_CAPTURE;
    }
}
