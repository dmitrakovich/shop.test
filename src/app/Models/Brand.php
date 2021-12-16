<?php

namespace App\Models;

use App\Traits\AttributeFilterTrait;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use AttributeFilterTrait;

    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    protected static function getRelationColumn()
    {
        return 'brand_id';
    }
    // public function setLogoAttribute($logo)
    // {
    //     $this->attributes['logo'] = strtr($logo, ['brand_logos/' => '']);
    // }

    // public function getLogoAttribute($logo)
    // {
    //     return "brand_logos/$logo";
    // }

    /**
     * Make dafault brand
     *
     * @return self
     */
    public static function getDefault()
    {
        return self::make([
            'id' => 57,
            'name' => 'BAROCCO',
            'slug' => 'barocco',
        ]);
    }
}
