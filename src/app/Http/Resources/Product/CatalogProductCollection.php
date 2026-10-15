<?php

namespace App\Http\Resources\Product;

use App\Pagination\CatalogLengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CatalogProductCollection extends ResourceCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var class-string<CatalogProductResource>
     */
    public $collects = CatalogProductResource::class;

    /**
     * Transform the resource collection into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var CatalogLengthAwarePaginator<int, \App\Models\Product> $paginator */
        $paginator = $this->resource;

        return [
            'current_page' => $paginator->currentPage(),
            'data' => parent::toArray($request),
            'first_page_url' => $paginator->url(1),
            'from' => $paginator->firstItem(),
            'last_page' => $paginator->lastPage(),
            'last_page_url' => $paginator->url($paginator->lastPage()),
            'links' => $paginator->linkCollection()->toArray(),
            'next_page_url' => $paginator->nextPageUrl(),
            'path' => $paginator->path(),
            'per_page' => $paginator->perPage(),
            'prev_page_url' => $paginator->previousPageUrl(),
            'to' => $paginator->lastItem(),
            'total' => $paginator->total(),
            'minPrice' => $paginator->minPrice,
            'maxPrice' => $paginator->maxPrice,
        ];
    }
}
