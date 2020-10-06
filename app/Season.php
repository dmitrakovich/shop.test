<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Season extends Model
{
    use AttributeFilterTrait;

    public $timestamps = false;
    protected static $relationName = 'season';
    protected static $relationTable = 'seasons';
}
