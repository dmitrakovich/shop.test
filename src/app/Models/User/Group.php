<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * @property int $id
 * @property string $name
 * @property float $discount
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Group extends Model
{
    const REGISTERED = 1;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_groups';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['name', 'discount'];

    public static function boot()
    {
        parent::boot();
        self::saved(function ($model) {
            Cache::forget(config('cache_config.global_user_discounts.key'));
        });
    }

    /**
     * Default model data
     */
    public static function defaultData(): array
    {
        return [
            'name' => 'unknown',
            'discount' => 0,
        ];
    }
}
