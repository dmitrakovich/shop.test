<?php

namespace App\Models\Offline;

use App\Models\Stock;
use App\Models\Offline\DisplacementItem;
use App\Models\Orders\OrderItem;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;

class Displacement extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    /**
     * Retrieve the items associated with the order.
     *
     * @return Relations\BelongsToMany
     */
    public function orderItems(): Relations\BelongsToMany
    {
        return $this->belongsToMany(OrderItem::class, DisplacementItem::class, 'displacement_id', 'order_item_id');
    }

    /**
     * A description of the directionFromStock function.
     *
     * @return Relations\BelongsTo
     */
    public function directionFromStock(): Relations\BelongsTo {
        return $this->belongsTo(Stock::class, 'direction_from');
    }

    /**
     * Get the relation to the stock for the direction.
     *
     * @return Relations\BelongsTo
     */
    public function directionToStock(): Relations\BelongsTo {
        return $this->belongsTo(Stock::class, 'direction_to');
    }
}
