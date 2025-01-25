<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CatalogProductCollection extends ResourceCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = CatalogProductResource::class;

    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            ...$this->resource->toArray($request),
            'total' => $this->resource->totalCount,
            'minPrice' => $this->resource->minPrice,
            'maxPrice' => $this->resource->maxPrice,
        ];
    }
}
