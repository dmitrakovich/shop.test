<?php

namespace App\Models;

use App\Contracts\Filterable;
use App\Traits\AttributeFilterTrait;
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
 * @property string $model
 *
 * @property-read Url|null $url
 *
 * @implements Filterable<Color>
 */
class Color extends Model implements Filterable
{
    use AttributeFilterTrait;

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
}
