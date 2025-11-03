<?php

namespace App\Models\Promo;

use App\Enums\Promo\SettingType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property SettingType $type
 * @property array<int, int> $ids
 * @property float $percentage Процент скидки
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class SaleSetting extends Model
{
    use SoftDeletes;

    /**
     * Indicates if all mass assignment is enabled.
     *
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => SettingType::class,
            'ids' => 'array',
        ];
    }
}
