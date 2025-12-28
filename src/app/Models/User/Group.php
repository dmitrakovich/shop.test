<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * @property int $id
 * @property string $name
 * @property float $discount
 */
class Group extends Model
{
    public const int REGISTERED = 1;

    private const int BEFORE_2K = 2;

    private const int BEFORE_3K = 3;

    private const int BEFORE_5K = 4;

    private const int AFTER_5K = 5;

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

    /**
     * todo: refactor if
     * todo: в таблицу user_groups добавить поле отвечающее за сумму покупок для перехода в эту группу
     */
    public static function getGroupIdByPurchaseSum(float $sum): int
    {
        if ($sum >= 5000) {
            return self::AFTER_5K;
        }

        if ($sum >= 3000) {
            return self::BEFORE_5K;
        }

        if ($sum >= 2000) {
            return self::BEFORE_3K;
        }

        return self::BEFORE_2K;
    }
}
