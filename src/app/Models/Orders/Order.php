<?php

namespace App\Models\Orders;

use App\Admin\Models\Administrator;
use App\Enums\Order\OrderMethod;
use App\Enums\Order\OrderTypeEnum;
use App\Enums\Payment\OnlinePaymentStatusEnum;
use App\Models\Country;
use App\Models\Logs\OrderActionLog;
use App\Models\Logs\OrderDistributionLog;
use App\Models\Logs\SmsLog;
use App\Models\Payments\Installment;
use App\Models\Payments\OnlinePayment;
use App\Models\Stock;
use App\Models\User\Device;
use App\Models\User\User;
use Deliveries\DeliveryMethod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;
use Illuminate\Notifications\Notifiable;
use Payments\PaymentMethod;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string|null $device_id
 * @property string $first_name
 * @property string|null $last_name
 * @property string|null $patronymic_name
 * @property int|null $promocode_id
 * @property string|null $email
 * @property string $phone
 * @property string|null $comment
 * @property float $total_price
 * @property string $currency
 * @property float $rate
 * @property int|null $country_id
 * @property string|null $region
 * @property string|null $city
 * @property string|null $zip
 * @property string|null $user_addr
 * @property int|null $payment_id
 * @property float|null $payment_cost
 * @property int|null $delivery_id
 * @property int|null $stock_id Warehouse (Stock) from which the order will be picked up
 * @property float|null $delivery_cost
 * @property float|null $delivery_price
 * @property int|null $delivery_point_id
 * @property \App\Enums\Order\OrderMethod $order_method
 * @property string|null $utm_medium
 * @property string|null $utm_source
 * @property string|null $utm_campaign
 * @property string|null $utm_content
 * @property string|null $utm_term
 * @property string $status_key
 * @property \Illuminate\Support\Carbon $status_updated_at
 * @property int|null $admin_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $batch_id Номер партии
 * @property float|null $weight
 * @property \App\Enums\Order\OrderTypeEnum|null $order_type Типы заказа
 * @property string $user_full_name
 * @property ?string $installment_contract_date
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Orders\OrderItem[] $data
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Orders\OrderItem[] $items
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Orders\OrderItemExtended[] $itemsExtended
 * @property-read \App\Models\User\User|null $user
 * @property-read \App\Models\User\Device|null $device
 * @property-read \App\Models\Country|null $country
 * @property-read \Deliveries\DeliveryMethod|null $delivery
 * @property-read \App\Models\Stock|null $stock
 * @property-read \Payments\PaymentMethod|null $payment
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Payments\OnlinePayment[] $onlinePayments
 * @property-read \App\Models\Orders\OrderStatus|null $status
 * @property-read \App\Admin\Models\Administrator|null $admin
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Orders\OrderAdminComment[] $adminComments
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Logs\SmsLog[] $mailings
 * @property-read \App\Models\Orders\Batch|null $batch
 * @property-read \App\Models\Orders\OrderTrack|null $track
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Logs\OrderActionLog[] $logs
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Logs\OrderDistributionLog[] $distributionLogs
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
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
        'stock_id',
        'delivery_cost',
        'delivery_price',
        'delivery_point_id',
        'order_method',
        'order_type',
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
        'installment_contract_date',
        'user_full_name',
    ];

    public static $itemDepartureStatuses = [
        'installment', 'packaging', 'pickup', 'sent', 'fitting', 'complete', 'return', 'return_fitting',
    ];

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'delivery_cost' => 0.0,
        'delivery_price' => 0.0,
        'weight' => 0.0,
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'order_method' => OrderMethod::class,
        'order_type' => OrderTypeEnum::class,
        'status_updated_at' => 'datetime',
    ];

    /**
     * Fix for duplicate logging
     */
    public bool $isLoggingDone = false;

    /**
     * Товары заказа
     *
     * @deprecated
     */
    public function data(): Relations\HasMany
    {
        return $this->hasMany(OrderItem::class)
            ->with([
                'product' => fn ($query) => $query->withTrashed(),
                'size:id,name',
            ]);
    }

    /**
     * Order items
     */
    public function items(): Relations\HasMany
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
     */
    public function itemsExtended(): Relations\HasMany
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
     */
    public function country(): Relations\BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Order delivery method
     */
    public function delivery(): Relations\BelongsTo
    {
        return $this->belongsTo(DeliveryMethod::class);
    }

    /**
     * Stock from which the order will be picked up
     */
    public function stock(): Relations\BelongsTo
    {
        return $this->belongsTo(Stock::class);
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
     */
    public function status(): Relations\BelongsTo
    {
        return $this->belongsTo(OrderStatus::class);
    }

    /**
     * Admin user
     */
    public function admin(): Relations\BelongsTo
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
     */
    public function adminComments(): Relations\HasMany
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
     * Order distribution history
     */
    public function distributionLogs(): Relations\HasMany
    {
        return $this->hasMany(OrderDistributionLog::class);
    }

    /**
     * Get the user's full name.
     */
    public function getUserFullNameAttribute(): string
    {
        return "{$this->last_name} {$this->first_name} {$this->patronymic_name}";
    }

    /**
     * Retrieves the contract date from the installment items of the current model.
     *
     * @return string|null The contract date or null if not found.
     */
    public function getInstallmentContractDateAttribute(): ?string
    {
        $contractDate = null;
        if ($this->relationLoaded('itemsExtended')) {
            foreach ($this->itemsExtended as $itemExtended) {
                if (isset($itemExtended->installment->contract_date)) {
                    $contractDate = $itemExtended->installment->contract_date;
                    break;
                }
            }
        }

        return $contractDate;
    }

    public function setInstallmentContractDateAttribute($value) {}

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
        $this->loadMissing([
            'itemsExtended' => fn ($query) => $query
                ->whereIn('status_key', self::$itemDepartureStatuses)
                ->with('installment'),
        ]);
        $deliveryPrice = $this->delivery_price ? $this->delivery_price : 0;
        $onlinePaymentsSum = $this->getAmountPaidOrders();
        $resultItemPrice = 0;
        $items = $this->itemsExtended->whereIn('status_key', self::$itemDepartureStatuses);
        $uniqItemsCount = $this->getUniqItemsCount();
        foreach ($items as $item) {
            $itemPrice = $item->current_price;
            $itemPrice += $deliveryPrice ? ($deliveryPrice / $uniqItemsCount) : 0;
            $itemPrice -= $onlinePaymentsSum ? ($onlinePaymentsSum / $uniqItemsCount) : 0;
            if ($this->hasInstallment() && $item->installment_num_payments) {
                $itemPrice = ($itemPrice - (($item->installment_num_payments - 1) * $item->installment_monthly_fee));
            }
            $resultItemPrice += $itemPrice;
        }

        return $resultItemPrice;
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

    /**
     * Check if the payment method is an installment.
     */
    public function hasInstallment(): bool
    {
        return $this->payment_id === Installment::PAYMENT_METHOD_ID;
    }
}
