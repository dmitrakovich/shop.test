<?php

namespace App\Models\Logs;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property int $product_id
 * @property string $action
 * @property array|null $added_sizes
 * @property array|null $removed_sizes
 * @property \Illuminate\Support\Carbon|null $created_at
 *
 * @property-read \App\Models\Product|null $product
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class InventoryLog extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'log_inventories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
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
        'added_sizes' => 'array',
        'removed_sizes' => 'array',
    ];

    /**
     * The action to restore inventory.
     *
     * @var string
     */
    const ACTION_RESTORE = 'RESTORE';

    /**
     * The action to update inventory.
     *
     * @var string
     */
    const ACTION_UPDATE = 'UPDATE';

    /**
     * The action to delete inventory.
     *
     * @var string
     */
    const ACTION_DELETE = 'DELETE';

    /**
     * Get the product that the inventory log belongs to.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)
            ->with(['category:id,title', 'brand:id,name'])
            ->withTrashed();
    }

    /**
     * Format sizes as a comma-separated string.
     */
    public function formatSizes(?array $sizes, Collection $sizeNames): ?string
    {
        if (empty($sizes)) {
            return null;
        }

        return implode(', ', array_map(fn ($sizeId) => $sizeNames[$sizeId], $sizes));
    }

    /**
     * Farmat date in admin panel
     */
    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format('d.m.Y H:i:s');
    }
}
