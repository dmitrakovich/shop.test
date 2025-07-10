<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ItemNotFoundException;

/**
 * @property string $key
 * @property array $config
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Config extends Model
{
    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'key';

    /**
     * Indicates if the model's ID is auto-incrementing.
     */
    public $incrementing = false;

    /**
     * The data type of the auto-incrementing ID.
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['key', 'config'];

    /**
     * The attributes that should be cast.
     */
    protected $casts = ['config' => 'array'];

    /**
     * Bootstrap the model and its traits.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::saved(function (self $config) {
            Cache::forget('config.' . $config->key);
        });
    }

    /**
     * Find a cached config by its primary key or throw an exception.
     *
     * @throws \Exception
     */
    public static function findCacheable(string $key): array
    {
        return Cache::rememberForever(
            "config.$key",
            fn () => self::findOrException($key)->config
        );
    }

    /**
     * Find a config by its primary key or throw an exception.
     *
     * @throws \Exception
     */
    private static function findOrException(string $key): self
    {
        if (empty($config = self::query()->find($key))) {
            throw new ItemNotFoundException("Config with key '$key' not found");
        }

        return $config;
    }
}
