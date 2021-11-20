<?php

namespace App\Models;

use Deliveries\DeliveryMethod;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Payments\PaymentMethod;

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
 * @property integer $delivery_point_id
 * @property string $order_method
 * @property string $utm_medium
 * @property string $utm_source
 * @property string $utm_campaign
 * @property string $utm_content
 * @property string $utm_term
 * @property string $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read string $user_full_name
 */
class Order extends Model
{
    use HasFactory;

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
        'delivery_point_id',
        'order_method',
        'utm_medium',
        'utm_source',
        'utm_campaign',
        'utm_content',
        'utm_term',
        'status',
    ];

    protected $appends = [
        'user_full_name',
    ];

    /**
     * Товары заказа
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function data()
    {
        return $this->hasMany(OrderData::class)
            ->with([
                'product' => function ($query) { $query->withTrashed(); },
                'size:id,name'
            ]);
    }

    /**
     * Order country
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Order delivery method
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function delivery()
    {
        return $this->belongsTo(DeliveryMethod::class);
    }

    /**
     * Order payment method
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payment()
    {
        return $this->belongsTo(PaymentMethod::class);
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
     * @param \DateTimeInterface $date
     * @return string
     */
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('d.m.Y H:i:s');
    }
}
