<?php

namespace App\Models\Orders;

use App\Models\Enum\Enum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class OrderItemStatus extends Model implements Enum, Sortable
{
    use HasFactory;
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
