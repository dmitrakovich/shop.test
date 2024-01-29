<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;

/**
 * @property int $id
 * @property string $name Название группы тегов
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $tags
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class TagGroup extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Tags
     */
    public function tags(): Relations\HasMany
    {
        return $this->hasMany(Tag::class);
    }
}
