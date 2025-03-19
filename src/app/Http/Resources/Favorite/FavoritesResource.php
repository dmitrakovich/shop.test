<?php

namespace App\Http\Resources\Favorite;

use App\Http\Resources\Product\CatalogProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @todo extends CartResource
 * @property Collection|Product[] $resource
 */
class FavoritesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'total_count' => count($this->resource),
            'total_price' => $this->resource->sum(fn (Product $product) => $product->getPrice()),
            'total_old_price' => $this->resource->sum(fn (Product $product) => $product->getOldPrice()),
            'items' => CatalogProductResource::collection($this->resource),
        ];
    }
}
