<?php

namespace App\Traits;

use App\Models\Url;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @mixin Model
 */
trait AttributeFilterTrait
{
    /**
     * Elasticsearch catalog field for this filter, or null when not indexed.
     */
    public static function elasticField(): ?string
    {
        return null;
    }

    /**
     * Build Elasticsearch filter clauses for the selected URL filters.
     *
     * Default: OR of model ids on {@see elasticField()}.
     *
     * @param  array<string, Url>  $values
     * @return list<array<string, mixed>>
     */
    public static function elasticFilterClauses(array $values): array
    {
        $field = static::elasticField();
        if ($field === null || $values === []) {
            return [];
        }

        $ids = array_values(array_map(
            static fn (Url $url): int => (int)$url->model_id,
            $values,
        ));

        return self::elasticTermOrTerms($field, $ids);
    }

    /**
     * @return 'id'|'slug'
     */
    public static function elasticFacetKey(): string
    {
        return 'id';
    }

    /**
     * @return list<string>
     */
    public static function elasticFacetColumns(): array
    {
        return ['id', 'name', 'slug'];
    }

    /**
     * @return list<string>
     */
    public static function elasticFacetExtras(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    public static function elasticFacetWhere(): array
    {
        return [];
    }

    /**
     * @param  list<int|string>  $values
     * @return list<array<string, mixed>>
     */
    protected static function elasticTermOrTerms(string $field, array $values): array
    {
        if ($values === []) {
            return [];
        }

        return [
            count($values) === 1
                ? ['term' => [$field => $values[0]]]
                : ['terms' => [$field => $values]],
        ];
    }

    /**
     * Slug для фильтра
     *
     * @return MorphOne<Url, $this>
     */
    public function url(): MorphOne
    {
        return $this->morphOne(Url::class, 'model');
    }

    /**
     * Удалить отношение при удалении фильтра
     */
    public function delete()
    {
        $this->url()->delete();

        return parent::delete();
    }
}
