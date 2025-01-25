<?php

namespace App\Http\Resources\Product;

use App\Facades\Currency;
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
            'prices' => [
                'price' => $this->getFinalPrice(),
                'old_price' => $this->getFinalOldPrice(),
                'formatted_price' => $this->getFormattedPrice(),
                'formatted_old_price' => $this->getFormattedOldPrice(),
                'has_discount' => $this->hasDiscount(),
                'sale_percentage' => $this->getSalePercentage(),
                'sales' => $this->getSales(),
                'currency' => Currency::getCurrentCurrency(),
            ],

            'is_favorite' => $this->isFavorite(),
            'is_new' => $this->isNew(),
            'short_name' => $this->shortName(),

            'media' => MediaResource::collection($this->getMedia()),
        ];
    }
}
