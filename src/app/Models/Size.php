<?php

namespace App\Models;

use App\Contracts\Filterable;
use App\Traits\AttributeFilterTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $value
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $model
 *
 * @property-read \App\Models\Url|null $url
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Size extends Model implements Filterable
{
    use AttributeFilterTrait;

    final const ONE_SIZE_ID = 1;

    final const ONE_SIZE_SLUG = 'size-none';

    /**
     * The connection name for the model.
     *
     * @var string|null
     */
    protected $connection = 'mysql';

    /**
     * Indicates if all mass assignment is enabled.
     *
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Perform any actions required after the model boots.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('sort', fn (Builder $query) => $query->orderBy('value'));

        static::saved(function (self $size): void {
            $size->url()->updateOrCreate([], ['slug' => $size->slug]);
        });
    }

    /**
     * Generate filter badge name
     */
    public function getBadgeName(): string
    {
        return 'Размер: ' . $this->name;
    }

    public static function getFilters(): array
    {
        return (new self())->newQuery()
            ->where('is_active', true)
            ->get(['id', 'name', 'slug', 'value'])
            ->keyBy('slug')
            ->append(['model'])
            ->toArray();
    }
}
