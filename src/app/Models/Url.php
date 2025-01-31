<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Cache;

/**
 * @property string $slug
 * @property string $model_type
 * @property int $model_id
 * @property string|null $redirect
 *
 * @property-read \Illuminate\Database\Eloquent\Model|null $model
 * @property-read \Illuminate\Database\Eloquent\Model|null $filters
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
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
     */
    public static function search(string $slug): ?self
    {
        // return Cache::tags(['catalog_slugs'])
        // ->rememberForever($slug, function () use ($slug) {
        return self::query()->find($slug);
        // }
        // );
        // if (!empty($url->redirect)) return redirect($url->redirect);
    }

    /**
     * Get the related model
     */
    public function model(): MorphTo
    {
        return $this->morphTo()->withTrashed();
    }

    /**
     * Get the parent filters model
     */
    public function filters(): MorphTo
    {
        return $this->morphTo('filters', 'model_type', 'model_id');
    }

    /**
     * Return relation model
     */
    public function getFilterModel(): Model
    {
        return $this->filters;
    }
}
