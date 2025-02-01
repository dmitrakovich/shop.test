<?php

namespace App\Http\Resources\Cart;

use App\Http\Resources\Product\CartProductResource;
use App\Http\Resources\Product\SizeResource;
use App\Models\CartData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CartData
 */
class CartItemResource extends JsonResource
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
            'count' => $this->count,
            'product' => new CartProductResource($this->product),
            'size' => new SizeResource($this->size),
        ];
    }
}
