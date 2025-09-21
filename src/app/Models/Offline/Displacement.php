<?php

namespace App\Models\Offline;

use App\Models\Orders\OrderItem;
use App\Models\Stock;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;

/**
 * @property int $id
 * @property int|null $direction_from Направление откуда
 * @property int|null $direction_to Направление куда
 * @property string|null $dispatch_date Дата отправки
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Orders\OrderItem[] $orderItems
 * @property-read \App\Models\Stock|null $directionFromStock
 * @property-read \App\Models\Stock|null $directionToStock
 */
class Displacement extends Model
{
    /**
     * Indicates if all mass assignment is enabled.
     *
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * Retrieve the items associated with the order.
     */
    public function orderItems(): Relations\BelongsToMany
    {
        return $this->belongsToMany(OrderItem::class, DisplacementItem::class, 'displacement_id', 'order_item_id');
    }

    /**
     * A description of the directionFromStock function.
     */
    public function directionFromStock(): Relations\BelongsTo
    {
        return $this->belongsTo(Stock::class, 'direction_from');
    }

    /**
     * Get the relation to the stock for the direction.
     */
    public function directionToStock(): Relations\BelongsTo
    {
        return $this->belongsTo(Stock::class, 'direction_to');
    }
}
