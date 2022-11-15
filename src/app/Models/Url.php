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
        'slug', 'model_type', 'model_id',
    ];

    /**
     * Найти url model по slug
     *
     * @param  string  $slug
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

    /**
     * Return ralation model
     *
     * @return Model
     */
    public function getFilterModel(): Model
    {
        return $this->filters;
    }
}
