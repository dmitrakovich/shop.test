<?php

namespace App\Http\Resources\Order;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Orders\Order
 */
class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'total_price' => $this->getTotalPrice(),
            'delivery_name' => $this->delivery?->name,
            'payment_name' => $this->payment?->name,
            'user_address' => $this->user_addr,
            'created_at' => $this->created_at,
        ];
    }
}
