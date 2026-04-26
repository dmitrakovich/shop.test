<?php

namespace App\Models;

use App\Contracts\Filterable;
use App\Traits\AttributeFilterTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $seo
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $tag_group_id Номер группы
 * @property string $model
 *
 * @property-read Collection|Product[] $products
 * @property-read TagGroup|null $group
 * @property-read Url|null $url
 *
 * @implements Filterable<Tag>
 */
class Tag extends Model implements Filterable
{
    use AttributeFilterTrait;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'seo',
        'tag_group_id',
    ];

    protected static function booted(): void
    {
        static::saved(function (self $tag): void {
            $tag->url()->updateOrCreate([], ['slug' => $tag->slug]);
        });
    }

    /**
     * Теги
     */
    public function products(): Relations\MorphToMany
    {
        return $this->morphToMany(Product::class, 'attribute', 'product_attributes');
    }

    /**
     * Tag group
     */
    public function group(): Relations\BelongsTo
    {
        return $this->belongsTo(TagGroup::class, 'tag_group_id');
    }
}
