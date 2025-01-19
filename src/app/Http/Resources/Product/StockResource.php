<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\AvailableSizes
 */
class StockResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $stock = $this->stock;

        return [
            'id' => $stock->id,
            'name' => $stock->name,
            'address' => $stock->address,
            'sizes' => $this->getFormattedSizes(), // todo: array
        ];
    }
}
