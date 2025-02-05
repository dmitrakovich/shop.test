<?php

namespace App\Http\Resources\Price;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Product
 */
class ProductPricesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'price' => $this->getFinalPrice(),
            'old_price' => $this->getFinalOldPrice(),
            'has_discount' => $this->hasDiscount(),
            'sale_percentage' => $this->getSalePercentage(),
            'sales' => $this->getSales(),
        ];
    }
}
