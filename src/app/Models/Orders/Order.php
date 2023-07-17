<?php

namespace App\Models\Orders;

use App\Enums\Payment\OnlinePaymentStatusEnum;
use App\Models\Country;
use App\Models\Device;
use App\Models\Enum\OrderMethod;
use App\Models\Logs\OrderActionLog;
use App\Models\Logs\SmsLog;
use App\Models\Payments\Installment;
use App\Models\Payments\OnlinePayment;
use App\Models\User\User;
use Deliveries\DeliveryMethod;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;
use Illuminate\Notifications\Notifiable;
use Payments\PaymentMethod;

/**
 * class Order
 *
 * @property int $id
 * @property int $user_id
 * @property string $first_name
 * @property string $last_name
 * @property string $patronymic_name
 * @property int $promocode_id
 * @property string $email
 * @property string $phone
 * @property string $comment
 * @property float $total_price
 * @property string $currency
 * @property float $rate
 * @property string $country
 * @property string $region
 * @property string $city
 * @property string $zip
 * @property string $user_addr
 * @property int $payment_id
 * @property float $payment_cost
 * @property int $delivery_id
 * @property float $delivery_cost
 * @property float $delivery_price
 * @property int $delivery_point_id
 * @property string $order_method
 * @property string $utm_medium
 * @property string $utm_source
 * @property string $utm_campaign
 * @property string $utm_content
 * @property string $utm_term
 * @property string $status_key
 * @property \Carbon\Carbon $status_updated_at
 * @property int $admin_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read User $user
 * @property-read Device $device
 * @property-read string $user_full_name
 * @property-read OrderStatus $status
 * @property-read Administrator $admin
 */
class Order extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'patronymic_name',
        'promocode_id',
        'email',
        'phone',
        'comment',
        'total_price',
        'currency',
        'rate',
        'country_id',
        'region',
        'city',
        'zip',
        'user_addr',
        'payment_id',
        'payment_cost',
        'delivery_id',
        'delivery_cost',
        'delivery_price',
        'delivery_point_id',
        'order_method',
        'utm_medium',
        'utm_source',
        'utm_campaign',
        'utm_content',
        'utm_term',
        'status_key',
        'status_updated_at',
        'admin_id',
        'created_at',
    ];

    protected $appends = [
        'user_full_name',
    ];

    /**
     * Fix for duplicate logging
     */
    public bool $isLoggingDone = false;

    /**
     * Товары заказа
     *
     * @deprecated
     *
     * @return Relations\HasMany
     */
    public function data()
    {
        return $this->hasMany(OrderItem::class)
            ->with([
                'product' => fn ($query) => $query->withTrashed(),
                'size:id,name',
            ]);
    }

    /**
     * Order items
     *
     * @return Relations\HasMany
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class)
            ->with([
                'product' => fn ($query) => $query->withTrashed(),
                'status:key,name_for_admin,name_for_user',
                'size:id,name',
            ]);
    }

    /**
     * Order items extended
     *
     * @return Relations\HasMany
     */
    public function itemsExtended()
    {
        return $this->hasMany(OrderItemExtended::class)
            ->with([
                'product' => fn ($query) => $query->withTrashed(),
                'status:key,name_for_admin,name_for_user',
                'size:id,name',
            ]);
    }

    /**
     * The authorized user who made the order
     */
    public function user(): Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The device from which the order was made
     */
    public function device(): Relations\BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * Order country
     *
     * @return Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Order delivery method
     *
     * @return Relations\BelongsTo
     */
    public function delivery()
    {
        return $this->belongsTo(DeliveryMethod::class);
    }

    /**
     * Order payment method
     */
    public function payment(): Relations\BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Order online payments
     */
    public function onlinePayments(): Relations\HasMany
    {
        return $this->hasMany(OnlinePayment::class);
    }

    /**
     * Order status
     *
     * @return Relations\BelongsTo
     */
    public function status()
    {
        return $this->belongsTo(OrderStatus::class);
    }

    /**
     * Admin user
     *
     * @return Relations\BelongsTo
     */
    public function admin()
    {
        return $this->belongsTo(Administrator::class);
    }

    /**
     * Get the admin associated with the model.
     */
    public function getAdmin(): Administrator
    {
        if (empty($this->admin) && !empty($this->admin_id)) {
            $this->load('admin');
        }

        return $this->admin ?? new Administrator(['id' => 0, 'name' => 'SYSTEM']);
    }

    /**
     * Get the previous admin associated with the model.
     */
    public function getPrevAdmin(): Administrator
    {
        if ($this->isClean('admin_id')) {
            return $this->getAdmin();
        }

        return Administrator::query()->where('id', $this->getOriginal('admin_id'))->first()
            ?? new Administrator(['id' => 0, 'name' => 'SYSTEM']);
    }

    /**
     * Admin comments log
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function adminComments()
    {
        return $this->hasMany(OrderAdminComment::class);
    }

    /**
     * Mailings sent by order
     */
    public function mailings(): Relations\HasMany
    {
        return $this->hasMany(SmsLog::class);
    }

    /**
     * Batch
     */
    public function batch(): Relations\BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    /**
     * Track number
     */
    public function track(): Relations\HasOne
    {
        return $this->hasOne(OrderTrack::class);
    }

    /**
     * Order actions history
     */
    public function logs(): Relations\HasMany
    {
        return $this->hasMany(OrderActionLog::class);
    }

    /**
     * Get the user's full name.
     *
     * @return string
     */
    public function getUserFullNameAttribute()
    {
        return "{$this->last_name} {$this->first_name} {$this->patronymic_name}";
    }

    public function getItemsPrice(): float
    {
        $price = 0;
        foreach ($this->data as $item) {
            $price += ($item->current_price * $item->count);
        }

        return $price;
    }

    public function getMaxItemsPrice(): float
    {
        $price = 0;
        foreach ($this->data as $item) {
            $price += ($item->old_price * $item->count);
        }

        return $price;
    }

    public function getTotalPrice(): float
    {
        $price = $this->total_price > 0 ? $this->total_price : $this->getItemsPrice();

        // учесть стоимость доставки
        // учесть коммиссию оплаты

        return $price;
    }

    /**
     * Get the amount of paid orders.
     */
    public function getAmountPaidOrders(): float
    {
        $this->loadMissing(['onlinePayments']);
        $price = $this->onlinePayments->where('last_status_enum_id', OnlinePaymentStatusEnum::SUCCEEDED)->sum('amount');

        return $price;
    }

    /**
     * Get total COD amount.
     */
    public function getTotalCODSum(): float
    {
        $deliveryPrice = $this->delivery_price ? $this->delivery_price : 0;
        $onlinePaymentsSum = $this->getAmountPaidOrders();

        if ((int)$this->payment_id === Installment::PAYMENT_METHOD_ID) {
            return $this->getInstallmentMonthlyFeeSum()  - $onlinePaymentsSum;
        } else {
            return $this->getItemsPrice() - $onlinePaymentsSum;
        }
    }

    /**
     * Get installment monthly fee sum.
     */
    public function getInstallmentMonthlyFeeSum(): float
    {
        $price = 0;
        $this->loadMissing(['itemsExtended']);
        foreach ($this->itemsExtended as $item) {
            $price += (float)$item->installment_monthly_fee;
        }

        return $price;
    }

    public function getUniqItemsCount(): int
    {
        $items = [];
        foreach ($this->data as $item) {
            $items[$item->product_id] = $item->product_id;
        }
        return count($items);
    }

    public function getItemsCount(): int
    {
        $count = 0;
        foreach ($this->data as $item) {
            $count += $item->count;
        }

        return $count;
    }

    /**
     * Prepare a date for array / JSON serialization.
     */
    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format('d.m.Y H:i:s');
    }

    /**
     * Check is this order made in one click
     */
    public function isOneClick(): bool
    {
        return $this->order_method == OrderMethod::ONECLICK;
    }

    /**
     * Check if this order has been completed
     */
    public function isCompleted(): bool
    {
        return $this->status_key === 'complete';
    }

    /**
     * Check if this order has been canceled
     */
    public function isCanceled(): bool
    {
        return $this->status_key === 'canceled';
    }

    /**
     * Route notifications for the SmsTraffic channel.
     *
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return string
     */
    public function routeNotificationForSmsTraffic($notification)
    {
        return $this->phone;
    }
}
