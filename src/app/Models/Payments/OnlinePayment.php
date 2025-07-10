<?php

namespace App\Models\Payments;

use App\Admin\Models\Administrator;
use App\Enums\Payment\OnlinePaymentMethodEnum;
use App\Enums\Payment\OnlinePaymentStatusEnum;
use App\Models\Orders\Order;
use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;

/**
 * @property int $id
 * @property int $order_id Номер заказа
 * @property string|null $currency_code Код валюты в ISO 4217
 * @property float $currency_value Валютный курс
 * @property \App\Enums\Payment\OnlinePaymentMethodEnum|null $method_enum_id enum ID платежной системы
 * @property int|null $admin_user_id ID admin пользователя
 * @property float|null $amount Сумма оплаты
 * @property string|null $expires_at Время жизни платежа
 * @property string|null $payment_id ID платежа в платежной системе
 * @property string|null $payment_num Номер платежа
 * @property string|null $payment_url Ссылка на платеж
 * @property int|null $card_last4 Последние 4 цифры карты
 * @property string|null $card_type Тип карты
 * @property string|null $email Email плательщика
 * @property string|null $phone Телефон плательщика
 * @property string|null $fio ФИО плательщика
 * @property string|null $comment Комментарий
 * @property bool|null $is_test Тестовый платеж
 * @property string|null $payed_at Дата оплаты платежа
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $qr_code QR code платежа
 * @property string|null $link_code Уникальный код ссылки на платеж
 * @property string|null $link_expires_at Времся жизни ссылки на платеж
 * @property array|null $request_data
 * @property \App\Enums\Payment\OnlinePaymentStatusEnum|null $last_status_enum_id enum ID последнего статуса платежа
 * @property float|null $paid_amount Оплаченная клиентом сумма
 * @property ?string $link
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Payments\OnlinePaymentStatus[] $statuses
 * @property-read \App\Models\Payments\OnlinePaymentStatus|null $lastStatus
 * @property-read \App\Models\Payments\OnlinePaymentStatus|null $lastCanceledStatus
 * @property-read \App\Models\Payments\OnlinePaymentStatus|null $lastSucceededStatus
 * @property-read \App\Models\Orders\Order|null $order
 * @property-read \App\Admin\Models\Administrator|null $admin
 */
class OnlinePayment extends Model
{
    protected $guarded = ['id'];

    protected $appends = [
        'link',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'method_enum_id' => OnlinePaymentMethodEnum::class,
        'is_test' => 'boolean',
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
