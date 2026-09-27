<?php

namespace App\Services\Elasticsearch;

use App\Helpers\KeyboardLayoutHelper;
use Elastic\Adapter\Documents\DocumentManager;
use Elastic\Adapter\Search\SearchParameters;
use Elastic\Adapter\Search\SearchResult;
use Elastic\Adapter\Search\Suggestion;
use Illuminate\Support\Collection;

class CatalogSuggestionService
{
    private const int SUGGESTION_LIMIT = 10;

    public function __construct(
        private readonly DocumentManager $documents,
    ) {}

    /**
     * @return list<string>
     */
    public function suggest(string $query): array
    {
        $query = trim($query);
        if ($query === '') {
            return [];
        }

        $result = $this->documents->search($this->buildSearchParameters($query));

        return $this->texts($result, 'phrases') ?: $this->texts($result, 'phrases_layout');
    }

    public function buildSearchParameters(string $query): SearchParameters
    {
        $suggest = [
            'phrases' => $this->completion($query),
        ];

        $switched = KeyboardLayoutHelper::switch($query);
        if ($switched !== null) {
            $suggest['phrases_layout'] = $this->completion($switched);
        }

        return (new SearchParameters())
            ->indices([(string)config('catalog.elasticsearch.suggestions_alias')])
            ->size(0)
            ->source(['text'])
            ->suggest($suggest);
    }

    /**
     * @return array<string, mixed>
     */
    private function completion(string $prefix): array
    {
        return [
            'prefix' => $prefix,
            'completion' => [
                'field' => 'suggest',
                'size' => self::SUGGESTION_LIMIT,
                'skip_duplicates' => true,
                'fuzzy' => [
                    'fuzziness' => 'AUTO',
                    'unicode_aware' => true,
                ],
            ],
        ];
    }

    /**
     * @return list<string>
     */
    private function texts(SearchResult $result, string $suggester): array
    {
        /** @var Collection<int, Suggestion> $suggestions */
        $suggestions = $result->suggestions()->get($suggester, new Collection());

        /** @var list<string> $texts */
        $texts = $suggestions
            ->flatMap(static fn (Suggestion $suggestion): Collection => $suggestion->options()->pluck('_source.text'))
            ->unique()
            ->values()
            ->all();

        return $texts;
    }
}
