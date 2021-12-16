<?php

namespace App\Models\Orders;

/**
 * class OrderItemExtended
 *
 * @property-read OrderItemStatus $status
 */
class OrderItemExtended extends OrderItem
{
    protected $appends = [
        'product_name',
        'product_photo',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'order_items';

    /**
     * Get the class name for polymorphic relations.
     *
     * @return string
     */
    public function getMorphClass()
    {
        return OrderItem::class;
    }

    /**
     * Product name
     *
     * @return string
     */
    public function getProductNameAttribute(): string
    {
        return $this->product->getFullName();
    }

    /**
     * Product photo
     *
     * @return string
     */
    public function getProductPhotoAttribute(): string
    {
        return $this->product->getFirstMediaUrl();
    }
}
