<?php

namespace App\Models;

use App\Traits\AttributeFilterTrait;
use Illuminate\Database\Eloquent\Model;

class Fabric extends Model
{
    use AttributeFilterTrait;

    public $timestamps = false;
}
