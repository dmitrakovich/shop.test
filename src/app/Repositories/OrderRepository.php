<?php

namespace App\Repositories;

use App\Models\Orders\Order;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class OrderRepository
{
    public function __construct(private readonly Order $model) {}

    /**
     * Get orders for user
     *
     * @return Collection|Order[]
     */
    public function getForUser(): Collection
    {
        return $this->model->newQuery()
            ->with([
                'delivery:id,name',
                'payment:id,name',
                'track:order_id,track_number,track_link',
                'onlinePayments:order_id,method_enum_id,payment_num,link_code,payment_url,last_status_enum_id',
                'items',
                'items.product.favorite', // todo: need only for catalogResource
                'items.product.sizes', // todo: optimize it
            ])
            ->where('user_id', Auth::id())
            ->orderBy('id', 'desc')
            ->get();
    }
}
