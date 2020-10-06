<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Url extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'slug';

    /**
     * Найти url model по slug
     *
     * @param string $slug
     * @return object
     */
    public static function search(string $slug)
    {
        return Cache::tags(['catalog_slugs'])
            ->rememberForever($slug, function () use ($slug) {
                return self::find($slug);
            }
        );
        // if (!empty($url->redirect)) return redirect($url->redirect);
    }
}
