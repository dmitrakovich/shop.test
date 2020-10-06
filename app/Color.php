<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    use AttributeFilterTrait;

    public $timestamps = false;
    protected static $relationName = 'color';
    protected static $relationTable = 'colors';
}
