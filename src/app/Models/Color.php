<?php

namespace App\Models;

use App\Contracts\Filterable;
use App\Traits\Filterable as FilterableTrait;
use Database\Factories\ColorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $value
 * @property string|null $seo
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Url|null $url
 *
 * @implements Filterable<Color>
 */
class Color extends Model implements Filterable
{
    use FilterableTrait;

    /** @use HasFactory<ColorFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'value',
        'seo',
    ];

    public static function elasticField(): ?string
    {
        return 'colors.id';
    }

    /**
     * @return list<string>
     */
    public static function elasticFacetColumns(): array
    {
        return ['id', 'name', 'slug', 'value'];
    }

    /**
     * @return list<string>
     */
    public static function elasticFacetExtras(): array
    {
        return ['value'];
    }
}
