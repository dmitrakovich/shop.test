<?php

namespace App\Sections\Catalog\Pipes;

use Closure;

/**
 * Pipe: page number from request (default 1).
 */
class ParsePagePipe
{
    /**
     * Parse page number (minimum 1).
     *
     * @param  array<string, mixed>  $passable  Pipeline context
     * @param  Closure  $next  Next pipe
     * @return array<string, mixed> Updated context
     */
    public function handle(array $passable, Closure $next): array
    {
        $request = $passable['request'];
        $state = $passable['state'];
        $page = $request->input('page');

        $state->page = max(1, (int)$page);

        $passable['state'] = $state;

        return $next($passable);
    }
}
