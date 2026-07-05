<?php

namespace App\Pagination;

use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @template TKey of array-key
 * @template TValue
 *
 * @extends LengthAwarePaginator<TKey, TValue>
 */
class CatalogLengthAwarePaginator extends LengthAwarePaginator
{
    public int $totalCount = 0;

    public float $minPrice = 0;

    public float $maxPrice = 0;

    /**
     * @param  LengthAwarePaginator<TKey, TValue>  $paginator
     * @return static
     */
    public static function fromPaginator(LengthAwarePaginator $paginator): static
    {
        /** @var static $catalogPaginator */
        $catalogPaginator = new self(
            $paginator->items(),
            $paginator->total(),
            $paginator->perPage(),
            $paginator->currentPage(),
            [
                'path' => $paginator->path(),
                'pageName' => $paginator->getPageName(),
            ],
        );

        return $catalogPaginator;
    }
}
