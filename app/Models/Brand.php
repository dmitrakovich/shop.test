<?php

namespace App\Models;

use App\Traits\AttributeFilterTrait;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use AttributeFilterTrait;

    public $timestamps = false;
    protected static $relationName = 'brand';
    protected static $relationTable = 'brands';

    // public function setLogoAttribute($logo)
    // {
    //     $this->attributes['logo'] = strtr($logo, ['brand_logos/' => '']);
    // }

    // public function getLogoAttribute($logo)
    // {
    //     return "brand_logos/$logo";
    // }
}
