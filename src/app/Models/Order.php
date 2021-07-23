<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_name',
        'type',
        'promocode_id',
        'email',
        'phone',
        'comment',
        'currency',
        'rate',
        'source',
        'country',
        'region',
        'city',
        'zip',
        'street',
        'house',
        'user_addr',
        'payment',
        'payment_code',
        'payment_cost',
        'delivery',
        'delivery_code',
        'delivery_cost',
        'delivery_point',
        'delivery_point_code',
        'user_id',
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
        $price = $this->getItemsPrice();

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
