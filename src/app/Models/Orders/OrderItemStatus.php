<?php

namespace App\Models\Orders;

use App\Models\Enum\Enum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

/**
 * @property string $key
 * @property string $name_for_admin
 * @property string $name_for_user
 * @property int $sorting
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Orders\OrderItemStatus ordered(string $direction = 'asc')
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class OrderItemStatus extends Model implements Enum, Sortable
{
    use SoftDeletes;
    use SortableTrait;

    const DEFAULT_VALUE = 'new';

    public $sortable = [
        'order_column_name' => 'sorting',
        'sort_when_creating' => true,
    ];

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'key';

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The data type of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    public static function getKeys(): array
    {
        return self::getValues();
    }

    public static function getValues(): array
    {
        return self::ordered()->pluck('key')->toArray();
    }

    public static function getDefaultValue()
    {
        return self::DEFAULT_VALUE;
    }
}
