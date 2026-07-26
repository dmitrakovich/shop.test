<?php

namespace App\Sections\Catalog\Pipelines;

use App\Data\Catalog\FilterStateData;
use App\Sections\Catalog\Pipes\BuildFilterTagsPipe;
use App\Sections\Catalog\Pipes\ParsePagePipe;
use App\Sections\Catalog\Pipes\ParsePricePipe;
use App\Sections\Catalog\Pipes\ParseSearchPipe;
use App\Sections\Catalog\Pipes\ParseSortPipe;
use App\Sections\Catalog\Pipes\ParseUrlFiltersPipe;
use App\Sections\Catalog\Services\PathSegmentParser;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;

/**
 * Pipeline for parsing catalog filters from an HTTP request.
 */
class FilterParsingPipeline
{
    /**
     * @param  Pipeline  $pipeline  Laravel Pipeline
     * @param  PathSegmentParser  $pathSegmentParser  Path segment parser
     */
    public function __construct(
        private readonly Pipeline $pipeline,
        private readonly PathSegmentParser $pathSegmentParser,
    ) {}

    /**
     * Parse filter state from request.
     *
     * @param  Request  $request  HTTP request
     * @param  array<string, mixed>  $dictionaries  Filter dictionaries
     * @return FilterStateData Filter state
     */
    public function parse(Request $request, array $dictionaries = []): FilterStateData
    {
        $passable = [
            'request' => $request,
            'state' => FilterStateData::create(),
            'segments' => $this->pathSegmentParser->parsePathSegments($request),
            'urlConfig' => config('catalog.url', []),
            'dictionaries' => $dictionaries,
        ];

        $result = $this->pipeline
            ->send($passable)
            ->through([
                ParseUrlFiltersPipe::class,
                ParseSearchPipe::class,
                ParsePricePipe::class,
                BuildFilterTagsPipe::class,
                ParseSortPipe::class,
                ParsePagePipe::class,
            ])
            ->thenReturn();

        return $result['state'];
    }
}
