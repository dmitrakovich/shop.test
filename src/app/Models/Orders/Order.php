<?php

namespace App\Models\Orders;

use App\Models\Device;
use App\Models\Country;
use Payments\PaymentMethod;
use Deliveries\DeliveryMethod;
use Illuminate\Support\Carbon;
use App\Models\Enum\OrderMethod;
use App\Models\Payments\OnlinePayment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * class Order
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $first_name
 * @property string $last_name
 * @property string $patronymic_name
 * @property integer $promocode_id
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
 * @property integer $payment_id
 * @property float $payment_cost
 * @property integer $delivery_id
 * @property float $delivery_cost
 * @property float $delivery_price
 * @property integer $delivery_point_id
 * @property string $order_method
 * @property string $utm_medium
 * @property string $utm_source
 * @property string $utm_campaign
 * @property string $utm_content
 * @property string $utm_term
 * @property string $status_key
 * @property integer $admin_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
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
        'admin_id',
        'created_at',
    ];

    protected $appends = [
        'user_full_name',
    ];

    /**
     * Товары заказа
     *
     * @deprecated
     * @return Relations\HasMany
     */
    public function data()
    {
        return $this->hasMany(OrderItem::class)
            ->with([
                'product' => function ($query) { $query->withTrashed(); },
                'size:id,name'
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
                'product' => function ($query) { $query->withTrashed(); },
                'status:key,name_for_admin,name_for_user',
                'size:id,name'
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
                'product' => function ($query) { $query->withTrashed(); },
                'status:key,name_for_admin,name_for_user',
                'size:id,name'
            ]);
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
     *
     * @return Relations\HasMany
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
     * Admin comments log
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function adminComments()
    {
        return $this->hasMany(OrderAdminComment::class);
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

    public function getItemsPrice()
    {
        $price = 0;
        foreach ($this->data as $item) {
            $price += ($item->current_price * $item->count);
        }
        return $price;
    }

    public function getMaxItemsPrice()
    {
        $price = 0;
        foreach ($this->data as $item) {
            $price += ($item->old_price * $item->count);
        }
        return $price;
    }


    public function getTotalPrice()
    {
        $price = $this->total_price > 0 ? $this->total_price : $this->getItemsPrice();

        // учесть стоимость доставки
        // учесть коммиссию оплаты

        return $price;
    }

    public function getItemsCount()
    {
        $count = 0;
        foreach ($this->data as $item) {
            $count += $item->count;
        }
        return $count;
    }
    /**
     * Farmat date in admin panel
     *
     * @return string
     */
    protected function serializeDate(\DateTimeInterface $date)
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
     * Route notifications for the SmsTraffic channel.
     *
     * @param  \Illuminate\Notifications\Notification $notification
     * @return string
     */
    public function routeNotificationForSmsTraffic($notification)
    {
        return $this->phone;
    }
}
