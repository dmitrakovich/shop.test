<?php

namespace App\Models;

use Database\Factories\TagGroupFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;

/**
 * @property int $id
 * @property string $name Название группы тегов
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $tags
 */
class TagGroup extends Model
{
    /** @use HasFactory<TagGroupFactory> */
    use HasFactory;

    /**
     * Indicates if all mass assignment is enabled.
     *
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * Tags
     *
     * @return Relations\HasMany<Tag, $this>
     */
    public function tags(): Relations\HasMany
    {
        return $this->hasMany(Tag::class);
    }
}
