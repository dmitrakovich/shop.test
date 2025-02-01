<?php

namespace App\Http\Resources\Product;

use App\Http\Resources\Price\PriceResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Product
 *
 * @todo merge with catalog resource
 */
class CartProductResource extends JsonResource
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
            'slug' => $this->slug,
            'sku' => $this->sku,
            'url' => $this->getUrl(),
            'color_txt' => $this->color_txt,

            'price' => new PriceResource($this->getFinalPrice()),
            'old_price' => new PriceResource($this->getFinalOldPrice()),
            'has_discount' => $this->hasDiscount(),
            'sale_percentage' => $this->getSalePercentage(),
            'sales' => $this->getSales(),

            'brand' => new BrandResource($this->brand),
            'category' => new CategoryResource($this->category),
            'media' => MediaResource::collection($this->getMedia()),
        ];
    }
}
