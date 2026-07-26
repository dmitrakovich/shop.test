<?php

namespace App\Sections\Catalog\Pipes;

use App\Enums\Product\ProductSort;
use Closure;

/**
 * Pipe: sort key from request with catalog config validation.
 */
class ParseSortPipe
{
    /**
     * Parse and validate sort key.
     *
     * @param  array<string, mixed>  $passable  Pipeline context
     * @param  Closure  $next  Next pipe
     * @return array<string, mixed> Updated context
     */
    public function handle(array $passable, Closure $next): array
    {
        $request = $passable['request'];
        $state = $passable['state'];
        $sortOptions = $passable['urlConfig']['sort_options'] ?? config('catalog.url.sort_options', []);
        $sort = $request->input('sort');

        if (is_string($sort) && $sort !== '' && is_array($sortOptions) && array_key_exists($sort, $sortOptions)) {
            $state->sort = $sort;
        } else {
            $state->sort = ProductSort::fromRequest(is_string($sort) ? $sort : null)->value;
        }

        $passable['state'] = $state;

        return $next($passable);
    }
}
