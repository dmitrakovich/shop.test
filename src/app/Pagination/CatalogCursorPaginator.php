<?php

namespace App\Pagination;

use Illuminate\Pagination\CursorPaginator;

/**
 * @template TKey of array-key
 * @template TValue
 *
 * @extends CursorPaginator<TKey, TValue>
 */
class CatalogCursorPaginator extends CursorPaginator
{
    public int $totalCount = 0;

    public float $minPrice = 0;

    public float $maxPrice = 0;

    /**
     * @param  CursorPaginator<TKey, TValue>  $paginator
     */
    public static function fromPaginator(CursorPaginator $paginator): static
    {
        /** @var static $catalogPaginator */
        $catalogPaginator = new self(
            $paginator->getCollection(),
            $paginator->perPage(),
            $paginator->cursor(),
            [
                'path' => $paginator->path(),
            ],
        );

        return $catalogPaginator;
    }
}
