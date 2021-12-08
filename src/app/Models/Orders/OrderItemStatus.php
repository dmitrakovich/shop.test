<?php

namespace App\Models\Orders;

use App\Models\Enum\Enum;
use Illuminate\Database\Eloquent\Model;
use Spatie\EloquentSortable\SortableTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderItemStatus extends Model implements Enum
{
    use SortableTrait;
    use HasFactory;
    use SoftDeletes;

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
