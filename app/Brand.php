<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use AttributeFilterTrait;

    public $timestamps = false;
    protected static $relationName = 'brand';
    protected static $relationTable = 'brands';
}
