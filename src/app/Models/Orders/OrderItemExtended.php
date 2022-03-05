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
        'product_link',
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
     * @deprecated
     * @return string
     */
    public function getProductNameAttribute(): string
    {
        return $this->product->getFullName();
    }

    /**
     * Product link
     *
     * @return string
     */
    public function getProductLinkAttribute(): string
    {
        return "<a href='{$this->product->getUrl()}' target='_blank'>{$this->product->extendedName()}</a>";
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
