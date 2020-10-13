<?php

namespace App\Models;

use App\Traits\AttributeFilterTrait;
use Illuminate\Database\Eloquent\Model;

class Heel extends Model
{
    use AttributeFilterTrait;

    public $timestamps = false;
}
