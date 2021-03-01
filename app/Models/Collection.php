<?php

namespace App\Models;

use App\Traits\AttributeFilterTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Collection extends Model
{
    use HasFactory, AttributeFilterTrait;

    protected static function getRelationColumn()
    {
        return 'collection_id';
    }
}
