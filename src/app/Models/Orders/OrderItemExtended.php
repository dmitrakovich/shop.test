<?php

namespace App\Models\Orders;

/**
 * class OrderItemExtended
 *
 * @property-read string $product_name
 * @property-read string $product_link
 * @property-read string $product_photo
 * @property-read int|null $installment_contract_number
 * @property-read float|null $installment_monthly_fee
 * @property-read bool|null $installment_send_notifications
 * @property-read int|null $stock_id
 * @property-read string|null $stock_name
 */
class OrderItemExtended extends OrderItem
{
    protected $appends = [
        'product_name',
        'product_link',
        'product_photo',
        'installment_contract_number',
        'installment_monthly_fee',
        'installment_send_notifications',
        'stock_id',
        'stock_name',
        'dispatch_date',
        'fulfilled_date',
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
     * Get the default foreign key name for the model.
     *
     * @return string
     */
    public function getForeignKey()
    {
        return 'order_item_id';
    }

    /**
     * Product name
     *
     * @deprecated
     */
    public function getProductNameAttribute(): string
    {
        return $this->product->getFullName();
    }

    /**
     * Product link
     */
    public function getProductLinkAttribute(): string
    {
        return "<a href='{$this->product->getUrl()}' target='_blank'>{$this->product->extendedName()}</a>";
    }

    /**
     * Product photo
     */
    public function getProductPhotoAttribute(): string
    {
        return $this->product->getFirstMediaUrl();
    }

    /**
     * Installment contract number for this order item
     */
    public function getInstallmentContractNumberAttribute(): ?int
    {
        return $this->installment?->contract_number;
    }

    /**
     * Installment contract number for this order item
     */
    public function getInstallmentMonthlyFeeAttribute(): ?float
    {
        return $this->installment?->monthly_fee;
    }

    /**
     * Installment contract number for this order item
     */
    public function getInstallmentSendNotificationsAttribute(): ?bool
    {
        return $this->installment?->send_notifications;
    }

    /**
     * Get the order's item's stock id.
     */
    public function getStockIdAttribute(): ?int
    {
        return $this->inventoryNotification?->stock_id;
    }

    /**
     * Get the dispatch date attribute for the order item.
     */
    public function getDispatchDateAttribute(): ?string
    {
        return $this->order->batch?->dispatch_date;
    }

    /**
     * Get the fulfilled date attribute for the order item.
     */
    public function getFulfilledDateAttribute(): ?string
    {
        return $this->inventoryNotification?->completed_at;
    }

    /**
     * Get the order's item's stock name.
     */
    public function getStockNameAttribute(): ?string
    {
        if (empty($stock = $this->inventoryNotification?->stock)) {
            return null;
        }

        return $stock->name . ' ' . $stock->address;
    }
}
