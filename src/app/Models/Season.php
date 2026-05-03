<?php

namespace App\Models;

use App\Contracts\Filterable;
use App\Traits\AttributeFilterTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $seo
 * @property bool $is_actual
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $model
 *
 * @property-read Url|null $url
 *
 * @implements Filterable<Season>
 */
class Season extends Model implements Filterable
{
    use AttributeFilterTrait;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'seo',
        'is_actual',
    ];

    protected static function booted(): void
    {
        static::saved(function (self $season): void {
            $season->url()->updateOrCreate([], ['slug' => $season->slug]);
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_actual' => 'boolean',
        ];
    }

    protected static function getRelationColumn(): string
    {
        return 'season_id';
    }
}
