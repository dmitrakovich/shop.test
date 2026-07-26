<?php

namespace App\Sections\Catalog\Pipes;

use Closure;

/**
 * Pipe: search query from request input/query.
 */
class ParseSearchPipe
{
    /**
     * Parse search string into state.
     *
     * @param  array<string, mixed>  $passable  Pipeline context
     * @param  Closure  $next  Next pipe
     * @return array<string, mixed> Updated context
     */
    public function handle(array $passable, Closure $next): array
    {
        $request = $passable['request'];
        $state = $passable['state'];
        $search = $request->input('search');

        if (is_string($search) && $search !== '') {
            $state->search = $search;
        }

        $passable['state'] = $state;

        return $next($passable);
    }
}
