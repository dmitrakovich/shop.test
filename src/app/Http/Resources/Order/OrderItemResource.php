<?php

namespace App\Http\Resources\Order;

use App\Http\Resources\Product\CatalogProductResource;
use App\Http\Resources\Product\SizeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Orders\OrderItem
 */
class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'status_name' => $this->status->getLabelForClient(),
            'current_price' => $this->current_price,
            'old_price' => $this->old_price,
            'discount' => $this->discount,
            'size' => new SizeResource($this->size),
            'product' => new CatalogProductResource($this->product),
        ];
    }
}
