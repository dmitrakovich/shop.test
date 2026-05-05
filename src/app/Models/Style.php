<?php

namespace App\Models;

use App\Contracts\Filterable;
use App\Traits\AttributeFilterTrait;
use Database\Factories\StyleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $seo
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $model
 *
 * @property-read Url|null $url
 *
 * @implements Filterable<Style>
 */
class Style extends Model implements Filterable
{
    use AttributeFilterTrait;

    /** @use HasFactory<StyleFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'seo',
    ];
}
