<?php

namespace App\Services\Elasticsearch;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\Tag;
use Elastic\Adapter\Documents\DocumentManager;
use Elastic\Adapter\Search\Aggregation;
use Elastic\Adapter\Search\SearchParameters;
use Illuminate\Support\Str;

class CatalogSuggestionBuilder
{
    public function __construct(
        private readonly DocumentManager $documents,
    ) {}

    /**
     * @return list<array{text: string, inputs: list<string>, weight: int}>
     */
    public function build(): array
    {
        /** @var array<string, mixed> $aggregations */
        $aggregations = $this->documents->search($this->aggregationParameters())
            ->aggregations()
            ->map(static fn (Aggregation $aggregation): array => $aggregation->raw())
            ->all();

        return $this->phrasesFromAggregations(
            $aggregations,
            Category::query()
                ->get(['id', 'title', 'parent_id'])
                ->mapWithKeys(static fn (Category $category): array => [$category->id => [
                    'title' => $category->title,
                    'parent_id' => $category->parent_id,
                ]])
                ->all(),
            $this->names(Color::class),
            $this->names(Tag::class),
            $this->names(Brand::class),
        );
    }

    public function aggregationParameters(): SearchParameters
    {
        return (new SearchParameters())
            ->indices([(string)config('catalog.elasticsearch.alias')])
            ->size(0)
            ->source(false)
            ->aggregations([
                'categories' => [
                    'terms' => [
                        'field' => 'categories.id',
                        'size' => 1000,
                    ],
                    'aggs' => [
                        'colors' => [
                            'terms' => [
                                'field' => 'colors.id',
                                'size' => 100,
                            ],
                        ],
                    ],
                ],
                'tags' => [
                    'terms' => [
                        'field' => 'tags.id',
                        'size' => 1000,
                    ],
                ],
                'brands' => [
                    'terms' => [
                        'field' => 'brand.id',
                        'size' => 1000,
                    ],
                ],
            ]);
    }

    /**
     * @param  array<string, mixed>  $aggregations
     * @param  array<int, array{title: string, parent_id: int|null}>  $categories
     * @param  array<int, string>  $colors
     * @param  array<int, string>  $tags
     * @param  array<int, string>  $brands
     * @return list<array{text: string, inputs: list<string>, weight: int}>
     */
    public function phrasesFromAggregations(
        array $aggregations,
        array $categories,
        array $colors,
        array $tags,
        array $brands,
    ): array {
        /** @var array<string, array{text: string, inputs: list<string>, weight: int}> $phrases */
        $phrases = [];

        foreach ($aggregations['categories']['buckets'] ?? [] as $bucket) {
            $categoryId = (int)$bucket['key'];
            $category = $categories[$categoryId] ?? null;
            if ($category === null || $categoryId === Category::ROOT_CATEGORY_ID) {
                continue;
            }

            $title = $this->normalize($category['title']);
            $this->addPhrase($phrases, $title, (int)$bucket['doc_count']);

            if (in_array($category['parent_id'], [null, Category::ROOT_CATEGORY_ID], true)) {
                continue;
            }

            foreach ($bucket['colors']['buckets'] ?? [] as $colorBucket) {
                $colorName = $colors[(int)$colorBucket['key']] ?? null;
                if ($colorName === null) {
                    continue;
                }

                $color = $this->pluralizeColor($this->normalize($colorName));
                $this->addPhrase($phrases, "{$title} {$color}", (int)$colorBucket['doc_count'], "{$color} {$title}");
            }
        }

        foreach (['tags' => $tags, 'brands' => $brands] as $aggregation => $names) {
            foreach ($aggregations[$aggregation]['buckets'] ?? [] as $bucket) {
                $name = $names[(int)$bucket['key']] ?? null;
                if ($name !== null) {
                    $this->addPhrase($phrases, $this->normalize($name), (int)$bucket['doc_count']);
                }
            }
        }

        return array_values($phrases);
    }

    /**
     * Completion only matches from the start of an input, so every tail of the phrase
     * is an input too ("каблуке" finds "туфли на каблуке").
     *
     * @param  array<string, array{text: string, inputs: list<string>, weight: int}>  $phrases
     */
    private function addPhrase(array &$phrases, string $text, int $weight, ?string $reversed = null): void
    {
        if (isset($phrases[$text])) {
            $phrases[$text]['weight'] = max($phrases[$text]['weight'], $weight);

            return;
        }

        $words = explode(' ', $text);
        $inputs = array_map(
            static fn (int $offset): string => implode(' ', array_slice($words, $offset)),
            array_keys($words),
        );

        if ($reversed !== null) {
            $inputs[] = $reversed;
        }

        $phrases[$text] = [
            'text' => $text,
            'inputs' => $inputs,
            'weight' => $weight,
        ];
    }

    private function normalize(string $name): string
    {
        return Str::squish(mb_strtolower(str_replace('"', '', $name)));
    }

    /**
     * Masculine singular color to plural: черный → черные, голубой → голубые, синий → синие.
     */
    private function pluralizeColor(string $name): string
    {
        return preg_replace(['/[ыо]й$/u', '/ий$/u'], ['ые', 'ие'], $name) ?? $name;
    }

    /**
     * @param  class-string<Color|Tag|Brand>  $model
     * @return array<int, string>
     */
    private function names(string $model): array
    {
        /** @var array<int, string> $names */
        $names = $model::query()->pluck('name', 'id')->all();

        return $names;
    }
}
