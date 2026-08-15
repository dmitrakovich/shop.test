<?php

namespace App\Models;

use App\Contracts\Filterable;
use App\Traits\Filterable as FilterableTrait;
use Database\Factories\SizeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $insole
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $model
 *
 * @property-read \App\Models\Url|null $url
 *
 * @implements Filterable<Size>
 */
class Size extends Model implements Filterable
{
    use FilterableTrait;

    /** @use HasFactory<SizeFactory> */
    use HasFactory;

    final const int ONE_SIZE_ID = 1;

    final const string ONE_SIZE_SLUG = 'size-none';

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
        static::addGlobalScope('sort', fn (Builder $query) => $query->orderBy('insole'));

        static::saved(function (self $size): void {
            $size->url()->updateOrCreate([], ['slug' => $size->slug]);
        });
    }

    public static function elasticField(): ?string
    {
        return 'sizes.id';
    }

    /**
     * @return list<string>
     */
    public static function elasticFacetColumns(): array
    {
        return ['id', 'name', 'slug', 'insole'];
    }

    /**
     * @return list<string>
     */
    public static function elasticFacetExtras(): array
    {
        return ['insole'];
    }

    /**
     * @return array<string, mixed>
     */
    public static function elasticFacetWhere(): array
    {
        return ['is_active' => true];
    }
}
