<?php

namespace App\Http\Resources\Product;

use App\Http\Resources\Price\ProductPricesResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Product
 */
class CatalogProductResource extends JsonResource
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
            'prices' => new ProductPricesResource($this->resource),

            'is_favorite' => $this->isFavorite(),
            'is_new' => $this->isNew(),
            'short_name' => $this->shortName(),

            'media' => MediaResource::collection($this->getMedia()),
        ];
    }
}
