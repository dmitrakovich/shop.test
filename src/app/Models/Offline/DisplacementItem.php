<?php

namespace App\Models\Offline;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int|null $displacement_id ID перемещения
 * @property int|null $order_item_id ID товара
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class DisplacementItem extends Model
{
    /**
     * Indicates if all mass assignment is enabled.
     *
     * @var bool
     */
    protected static $unguarded = true;
}
