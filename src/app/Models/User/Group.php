<?php

namespace App\Models\User;

use App\Enums\User\UserGroupTypeEnum;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Class Group
 *
 * @property int $id
 * @property string $name
 * @property float $discount
 */
class Group extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var  string
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
     *
     * @var array<int, string>
     */
    protected $fillable = ['name', 'discount', 'enum_type_id'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'enum_type_id' => UserGroupTypeEnum::class,
    ];

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
