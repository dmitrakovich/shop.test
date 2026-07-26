<?php

namespace App\Sections\Catalog\Pipes;

use Closure;

/**
 * Pipe: allow query price_min / price_max overrides.
 */
class ParsePricePipe
{
    /**
     * Override price bounds from query when present.
     *
     * @param  array<string, mixed>  $passable  Pipeline context
     * @param  Closure  $next  Next pipe
     * @return array<string, mixed> Updated context
     */
    public function handle(array $passable, Closure $next): array
    {
        $request = $passable['request'];
        $state = $passable['state'];

        $priceMin = $this->toFloat($request->input('price_min'));
        $priceMax = $this->toFloat($request->input('price_max'));

        if ($priceMin !== null) {
            $state->priceMin = $priceMin;
        }
        if ($priceMax !== null) {
            $state->priceMax = $priceMax;
        }

        $passable['state'] = $state;

        return $next($passable);
    }

    /**
     * Convert query value to float.
     *
     * @param  mixed  $value  Raw value
     * @return float|null Number or null
     */
    private function toFloat(mixed $value): ?float
    {
        if (!is_string($value) && !is_numeric($value)) {
            return null;
        }

        $normalized = str_replace(',', '.', (string)$value);
        if ($normalized === '' || !is_numeric($normalized)) {
            return null;
        }

        return (float)$normalized;
    }
}
