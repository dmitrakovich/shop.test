<?php

namespace App\Sections\Catalog\Services;

use Illuminate\Http\Request;

/**
 * Parse catalog path segments from the HTTP request.
 */
class PathSegmentParser
{
    /**
     * Parse catalog path segments (already without catalog prefix).
     *
     * @param  Request  $request  HTTP request
     * @return list<string> Path segments
     */
    public function parsePathSegments(Request $request): array
    {
        $path = (string)$request->route('path', '');

        return array_values(array_filter(explode('/', $path)));
    }
}
