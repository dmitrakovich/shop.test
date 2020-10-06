<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Style extends Model
{
    use AttributeFilterTrait;

    public $timestamps = false;
}
