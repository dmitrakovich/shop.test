<?php

namespace App\Models\Orders;

use App\Admin\Models\Administrator;
use App\Casts\AsPhone;
use App\Enums\Order\OrderItemStatus;
use App\Enums\Order\OrderMethod;
use App\Enums\Order\OrderStatus;
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
use App\ValueObjects\Phone;
use Deliveries\DeliveryMethod;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Payments\PaymentMethod;

/**
 * @property int $id
 * @property int|null $user_id
 * @property int|null $device_id
 * @property string $first_name
 * @property string|null $last_name
 * @property string|null $patronymic_name
 * @property int|null $promocode_id
 * @property string|null $email
 * @property Phone $phone
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
 * @property OrderMethod $order_method
 * @property string|null $utm_medium
 * @property string|null $utm_source
 * @property string|null $utm_campaign
 * @property string|null $utm_content
 * @property string|null $utm_term
 * @property OrderStatus $status
 * @property Carbon $status_updated_at
 * @property int|null $admin_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $batch_id Номер партии
 * @property int|null $belpost_item_id
 * @property string|null $belpost_s10code
 * @property float|null $weight
 * @property OrderTypeEnum|null $order_type Типы заказа
 * @property string $user_full_name
 * @property ?string $installment_contract_date
 *
 * @property-read Collection|OrderItem[] $data
 * @property-read Collection|OrderItem[] $items
 * @property-read Collection|OrderItemExtended[] $itemsExtended
 * @property-read User|null $user
 * @property-read Device|null $device
 * @property-read Country|null $country
 * @property-read DeliveryMethod|null $delivery
 * @property-read Stock|null $stock
 * @property-read PaymentMethod|null $payment
 * @property-read Collection|OnlinePayment[] $onlinePayments
 * @property-read Administrator|null $admin
 * @property-read Collection|OrderAdminComment[] $adminComments
 * @property-read Collection|SmsLog[] $mailings
 * @property-read Batch|null $batch
 * @property-read OrderTrack|null $track
 * @property-read Collection|OrderActionLog[] $logs
 * @property-read Collection|OrderDistributionLog[] $distributionLogs
 */
class Order extends Model
{
    use Notifiable;

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
        'status',
        'status_updated_at',
        'admin_id',
        'batch_id',
        'belpost_item_id',
        'belpost_s10code',
        'created_at',
    ];

    protected $appends = [
        'installment_contract_date',
        'user_full_name',
    ];

    /**
     * The model's attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'delivery_cost' => 0.0,
        'delivery_price' => 0.0,
        'weight' => 0.0,
    ];

    /**
     * Fix for duplicate logging
     */
    public bool $isLoggingDone = false;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'order_method' => OrderMethod::class,
            'order_type' => OrderTypeEnum::class,
            'status' => OrderStatus::class,
            'status_updated_at' => 'datetime',
            'phone' => AsPhone::class,
        ];
    }

    /**
     * Товары заказа
     *
     * @return Relations\HasMany<OrderItem, $this>
     */
    #[\Deprecated]
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
     *
     * @return Relations\HasMany<OrderItem, $this>
     */
    public function items(): Relations\HasMany
    {
        return $this->hasMany(OrderItem::class)
            ->with([
                'product' => fn ($query) => $query->withTrashed(),
                'size:id,name,slug',
            ]);
    }

    /**
     * Order items extended
     *
     * @return Relations\HasMany<OrderItemExtended, $this>
     */
    public function itemsExtended(): Relations\HasMany
    {
        return $this->hasMany(OrderItemExtended::class)
            ->with([
                'product' => fn ($query) => $query->withTrashed(),
                'size:id,name',
            ]);
    }

    /**
     * The authorized user who made the order
     *
     * @return Relations\BelongsTo<User, $this>
     */
    public function user(): Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The device from which the order was made
     *
     * @return Relations\BelongsTo<Device, $this>
     */
    public function device(): Relations\BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * Order country
     *
     * @return Relations\BelongsTo<Country, $this>
     */
    public function country(): Relations\BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Order delivery method
     *
     * @return Relations\BelongsTo<DeliveryMethod, $this>
     */
    public function delivery(): Relations\BelongsTo
    {
        return $this->belongsTo(DeliveryMethod::class);
    }

    /**
     * Stock from which the order will be picked up
     *
     * @return Relations\BelongsTo<Stock, $this>
     */
    public function stock(): Relations\BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    /**
     * Order payment method
     *
     * @return Relations\BelongsTo<PaymentMethod, $this>
     */
    public function payment(): Relations\BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Order online payments
     *
     * @return Relations\HasMany<OnlinePayment, $this>
     */
    public function onlinePayments(): Relations\HasMany
    {
        return $this->hasMany(OnlinePayment::class);
    }

    /**
     * Admin user
     *
     * @return Relations\BelongsTo<Administrator, $this>
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
     *
     * @return Relations\HasMany<OrderAdminComment, $this>
     */
    public function adminComments(): Relations\HasMany
    {
        return $this->hasMany(OrderAdminComment::class);
    }

    /**
     * Mailings sent by order
     *
     * @return Relations\HasMany<SmsLog, $this>
     */
    public function mailings(): Relations\HasMany
    {
        return $this->hasMany(SmsLog::class);
    }

    /**
     * Batch
     *
     * @return Relations\BelongsTo<Batch, $this>
     */
    public function batch(): Relations\BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    /**
     * Track number
     *
     * @return Relations\HasOne<OrderTrack, $this>
     */
    public function track(): Relations\HasOne
    {
        return $this->hasOne(OrderTrack::class);
    }

    /**
     * Order actions history
     *
     * @return Relations\HasMany<OrderActionLog, $this>
     */
    public function logs(): Relations\HasMany
    {
        return $this->hasMany(OrderActionLog::class);
    }

    /**
     * Order distribution history
     *
     * @return Relations\HasMany<OrderDistributionLog, $this>
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
     * Order weight is stored in kilograms; Belpost API expects grams.
     * When weight is missing or zero, defaults to 1 kg (1000 g).
     */
    public function getWeightInGrams(): int
    {
        $kg = (float)($this->weight ?: 0);
        if ($kg <= 0) {
            return 1000;
        }

        return max((int)round($kg * 1000), 1);
    }

    /**
     * Get total COD amount.
     */
    public function getTotalCODSum(): float
    {
        $this->loadMissing([
            'itemsExtended' => fn ($query) => $query
                ->whereIn('status', OrderItemStatus::departureStatuses())
                ->with('installment'),
        ]);
        $deliveryPrice = $this->delivery_price ?: 0;
        $onlinePaymentsSum = $this->getAmountPaidOrders();
        $resultItemPrice = 0;
        $items = $this->itemsExtended->whereIn('status', OrderItemStatus::departureStatuses());
        $uniqItemsCount = $this->getUniqItemsCount();
        foreach ($items as $item) {
            $itemPrice = $item->current_price;
            $itemPrice += $deliveryPrice ? ($deliveryPrice / $uniqItemsCount) : 0;
            $itemPrice -= $onlinePaymentsSum ? ($onlinePaymentsSum / $uniqItemsCount) : 0;
            if ($this->hasInstallment() && $item->installment_num_payments) {
                $itemPrice -= ($item->installment_num_payments - 1) * $item->installment_monthly_fee;
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
        return $this->status->isCompleted();
    }

    /**
     * Check if this order has been canceled
     */
    public function isCanceled(): bool
    {
        return $this->status->isCanceled();
    }

    /**
     * Route notifications for the SmsTraffic channel.
     *
     * @param  Notification  $notification
     * @return int
     */
    public function routeNotificationForSmsTraffic($notification)
    {
        return $this->phone->forSms();
    }

    /**
     * Check if the payment method is an installment.
     */
    public function hasInstallment(): bool
    {
        return $this->payment_id === Installment::PAYMENT_METHOD_ID;
    }
}
