<?php

namespace App\ElasticSearch\Indices\Product\Search;

class DefaultProductSearch
{
    /** @var list<string> Stopwords excluded from search */
    private const STOPWORDS = [
        'из', 'в', 'на', 'с', 'и', 'по', 'для', 'у', 'к', 'о', 'об', 'от', 'до',
        'за', 'при', 'без', 'под', 'над', 'через', 'или', 'но', 'а', 'как', 'что',
        'это', 'то', 'же', 'ли', 'бы',
    ];

    /**
     * Build Elasticsearch query for product search.
     *
     * @param  string|null  $search  Search text
     * @return array<string, mixed> ES query
     */
    public function getSearchQuery(?string $search = null): array
    {
        $result = [];
        $result['bool'] = [];

        if ($search) {
            $words = $this->normalizeSearchQueryToWords($search);
            if ($words === []) {
                $result['bool']['must'] = ['match_all' => (object)[]];

                return $result;
            }

            $fields = [
                'sku^12',
                'brand.name^7',
                'short_name^6',
                'categories.name^5',
                'description',
            ];

            $should = [];
            foreach ($words as $word) {
                $should[] = [
                    'multi_match' => [
                        'query' => $word,
                        'fields' => $fields,
                        'type' => 'best_fields',
                        'fuzziness' => 'AUTO',
                        'prefix_length' => 1,
                        'max_expansions' => 50,
                    ],
                ];
            }

            $result['bool']['should'] = $should;
            $n = count($words);
            $result['bool']['minimum_should_match'] = $n <= 2 ? $n : $n - 1;
        } else {
            $result['bool']['must'] = [
                'match_all' => (object)[],
            ];
        }

        return $result;
    }

    /**
     * Split search string into words without stopwords.
     *
     * @param  string  $search  Raw query
     * @return list<string> Normalized words
     */
    private function normalizeSearchQueryToWords(string $search): array
    {
        $s = preg_replace("/[.,\\\\\\[\\]\\/#!$%\\^&\\*;:{}=\\-_`~()\\+]/", ' ', trim(mb_strtolower($search)));
        $s = preg_replace('/\s+/', ' ', $s);
        $words = $s === '' ? [] : explode(' ', $s);

        return array_values(array_filter(
            $words,
            fn (string $w): bool => !in_array($w, self::STOPWORDS, true)
        ));
    }
}
