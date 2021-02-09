<?php

namespace App\Models;

use App\Traits\AttributeFilterTrait;
use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    use AttributeFilterTrait;

    public $timestamps = false;
}
