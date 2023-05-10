<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;

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
