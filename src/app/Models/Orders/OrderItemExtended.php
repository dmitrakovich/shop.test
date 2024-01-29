<?php

namespace App\Models\Orders;

/**
 * @property int $id
 * @property int $order_id
 * @property int $product_id
 * @property int $size_id
 * @property int $count
 * @property float $buy_price
 * @property float $price
 * @property float $old_price
 * @property float $current_price
 * @property float $discount
 * @property bool $promocode_applied
 * @property string $status_key
 * @property \Illuminate\Support\Carbon $status_updated_at
 * @property \Illuminate\Support\Carbon|null $release_date
 * @property bool|null $pred_period
 * @property string $product_name
 * @property string $product_link
 * @property string $product_photo
 * @property ?string $installment_contract_number
 * @property ?float $installment_monthly_fee
 * @property ?bool $installment_send_notifications
 * @property ?int $stock_id
 * @property ?string $dispatch_date
 * @property ?string $fulfilled_date
 * @property ?string $stock_name
 * @property string $item_status_key
 *
 * @property-read \App\Models\Orders\Order|null $order
 * @property-read \App\Models\Product|null $product
 * @property-read \App\Models\Size|null $size
 * @property-read \App\Models\Orders\OrderItemStatus|null $status
 * @property-read \App\Models\Payments\Installment|null $installment
 * @property-read \App\Models\Logs\OrderItemStatusLog|null $inventoryNotification
 * @property-read \App\Models\Logs\OrderItemStatusLog|null $statusLog
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
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
        'item_status_key',
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
    public function getInstallmentContractNumberAttribute(): ?string
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
        return $this->statusLog?->stock_id;
    }

    /**
     * Get the dispatch date attribute for the order item.
     */
    public function getDispatchDateAttribute(): ?string
    {
        return $this->statusLog?->sended_at;
    }

    /**
     * Get the fulfilled date attribute for the order item.
     */
    public function getFulfilledDateAttribute(): ?string
    {
        return $this->statusLog?->completed_at;
    }

    /**
     * Get the order's item's stock name.
     */
    public function getStockNameAttribute(): ?string
    {
        if (empty($stock = $this->statusLog?->stock)) {
            return null;
        }

        return $stock->name . ' ' . $stock->address;
    }

    /**
     * Accessor for stupid admin panel
     */
    public function getItemStatusKeyAttribute(): string
    {
        return $this->status_key;
    }

    /**
     * Mutator for stupid admin panel
     */
    public function setItemStatusKeyAttribute($value): void
    {
        $this->attributes['status_key'] = $value;
    }
}
