<?php

namespace App\Http\Resources\Product;

use App\Http\Resources\Price\ProductPricesResource;
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
            'prices' => new ProductPricesResource($this->resource),
            'brand' => new BrandResource($this->brand),
            'category' => new CategoryResource($this->category),
            'media' => MediaResource::collection($this->getMedia()),
        ];
    }
}
