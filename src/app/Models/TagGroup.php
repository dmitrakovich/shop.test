<?php

namespace App\Models;

use App\Models\Tag;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\{
    Model,
    Relations
};

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
