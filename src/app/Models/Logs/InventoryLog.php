<?php

namespace App\Models\Logs;

use App\Enums\Product\InventoryLogAction;
use App\Models\Product;
use Database\Factories\InventoryLogFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $product_id
 * @property InventoryLogAction $action
 * @property array|null $added_sizes
 * @property array|null $removed_sizes
 * @property \Illuminate\Support\Carbon|null $created_at
 *
 * @property-read \App\Models\Product|null $product
 */
#[UseFactory(InventoryLogFactory::class)]
class InventoryLog extends Model
{
    /** @use HasFactory<InventoryLogFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'log_inventories';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'product_id',
        'action',
        'added_sizes',
        'removed_sizes',
        'created_at',
    ];

    /**
     * The name of the "updated at" column.
     *
     * @var string|null
     */
    public const UPDATED_AT = null;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'action' => InventoryLogAction::class,
        'added_sizes' => 'array',
        'removed_sizes' => 'array',
    ];

    /**
     * Get the product that the inventory log belongs to.
     *
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)
            ->with(['category:id,title', 'brand:id,name'])
            ->withTrashed();
    }
}
