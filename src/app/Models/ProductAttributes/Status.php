<?php

namespace App\Models\ProductAttributes;

use App\Contracts\Filterable;
use App\Models\Url;
use App\Traits\Filterable as FilterableTrait;
use Database\Factories\StatusFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\Url|null $url
 *
 * @implements Filterable<Status>
 */
class Status extends Model implements Filterable
{
    use FilterableTrait;

    /** @use HasFactory<StatusFactory> */
    use HasFactory;

    /**
     * Indicates if all mass assignment is enabled.
     *
     * @var bool
     */
    protected static $unguarded = true;

    public static function elasticField(): ?string
    {
        return 'status_slugs';
    }

    /**
     * @return 'id'|'slug'
     */
    public static function elasticFacetKey(): string
    {
        return 'slug';
    }

    /**
     * @param  array<string, Url>  $values
     * @return list<array<string, mixed>>
     */
    public static function elasticFilterClauses(array $values): array
    {
        // `promotion` is Sale-driven and not indexed; ignore it on the ES path.
        $slugs = array_values(array_diff(array_keys($values), ['promotion']));

        return self::elasticTermOrTerms((string)static::elasticField(), $slugs);
    }

    /**
     * Prepare for page title
     */
    public function getForTitle(): string
    {
        return match ($this->slug) {
            'st-new' => '- новинки!',
            'st-sale' => 'на распродаже!',
            'promotion' => 'на акции',
        };
    }
}
