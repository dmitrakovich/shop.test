<?php

namespace App\Models\Orders;

use App\Enums\Belpost\BelpostBatchStatus;
use App\Enums\Belpost\BelpostDirection;
use App\Enums\Belpost\BelpostPaymentType;
use App\Enums\Belpost\BelpostPostalDeliveryType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string|null $dispatch_date Дата отправки
 * @property int|null $belpost_list_id
 * @property BelpostBatchStatus|null $belpost_status
 * @property string|null $name
 * @property BelpostPostalDeliveryType|string|null $postal_delivery_type
 * @property BelpostDirection|string|null $direction
 * @property BelpostPaymentType|string|null $payment_type
 * @property string|null $card_number
 * @property bool $negotiated_rate
 * @property bool $is_declared_value
 * @property bool $is_partial_receipt
 * @property int|null $belpost_document_id
 * @property string|null $belpost_sync_error
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Collection|Order[] $orders
 */
class Batch extends Model
{
    /**
     * Indicates if all mass assignment is enabled.
     *
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'dispatch_date' => 'datetime',
        'belpost_status' => BelpostBatchStatus::class,
        'postal_delivery_type' => BelpostPostalDeliveryType::class,
        'direction' => BelpostDirection::class,
        'payment_type' => BelpostPaymentType::class,
        'negotiated_rate' => 'boolean',
        'is_declared_value' => 'boolean',
        'is_partial_receipt' => 'boolean',
    ];

    /**
     * Orders
     *
     * @return Relations\HasMany<Order, $this>
     */
    public function orders(): Relations\HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function isBelpostEditable(): bool
    {
        return $this->belpost_status === null || $this->belpost_status->isEditable();
    }

    public function isBelpostCommitted(): bool
    {
        return in_array($this->belpost_status, [BelpostBatchStatus::Committed, BelpostBatchStatus::InOps], true);
    }

    public function canGenerateBelpostBlanks(): bool
    {
        if (!$this->isLinkedToBelpost()) {
            return false;
        }

        if ($this->isBelpostCommitted()) {
            return true;
        }

        return $this->dispatch_date !== null;
    }

    public function isLinkedToBelpost(): bool
    {
        return $this->belpost_list_id !== null;
    }

    public function allOrdersSyncedToBelpost(): bool
    {
        return !$this->orders()->whereNull('belpost_item_id')->exists();
    }

    public function unsyncedBelpostOrdersCount(): int
    {
        return $this->orders()->whereNull('belpost_item_id')->count();
    }
}
