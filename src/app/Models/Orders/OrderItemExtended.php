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
        'installment_contract_number',
        'installment_monthly_fee',
        'installment_send_notifications',
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
     *
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
}
