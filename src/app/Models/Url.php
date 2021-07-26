<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Url extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'slug';
    protected $keyType = 'string';
    protected $fillable = [
        'slug'
    ];
    /**
     * Найти url model по slug
     *
     * @param string $slug
     * @return object
     */
    public static function search(string $slug)
    {
        // return Cache::tags(['catalog_slugs'])
            // ->rememberForever($slug, function () use ($slug) {
                return self::find($slug);
            // }
        // );
        // if (!empty($url->redirect)) return redirect($url->redirect);
    }
    /**
     * Get the parent filters model
     */
    public function filters()
    {
        return $this->morphTo('filters', 'model_type', 'model_id');
    }
}