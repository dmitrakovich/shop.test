<?php

namespace App\Services\Order;

use App\Models\AvailableSizes;
use App\Models\Logs\OrderItemInventoryNotificationLog;
use App\Models\Orders\OrderItem;
use App\Models\Product;
use App\Models\Stock;
use App\Notifications\OrderItemInventoryNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class OrderItemInventoryService
 */
class OrderItemInventoryService
{
    /**
     * Possible statuses for which notifications are sent.
     */
    const STATUSES_FOR_NOTIFICATIONS = [
        // 'new',
        // 'canceled',
        'confirmed',
        // 'pickup',
        'complete',
        'installment',
        'return',
        'return_fitting',
    ];

    /**
     * Stocks priority (stock_id => priority)
     */
    private array $stocksPriority = [
        Stock::MINKS_ID => -1,
    ];

    /**
     * Handle the change of status for an order item and send notification if required.
     */
    public function handleChangeItemStatus(OrderItem $orderItem): void
    {
        if ($this->shouldSendNotification($orderItem)) {
            $chat = $orderItem->inventoryNotification->getChatByStatus($orderItem->status_key);
            $chat->notify(new OrderItemInventoryNotification($orderItem));
            $orderItem->inventoryNotification->setDateFieldForStatus($orderItem->status_key);
        }
    }

    /**
     * Check if a notification should be sent for the given order item and status.
     */
    protected function shouldSendNotification(OrderItem $orderItem): bool
    {
        $status = $orderItem->status_key;
        $notification = $orderItem->inventoryNotification;
        if (empty($notification) || empty($notification->getChatByStatus($status))) {
            return false;
        }

        $dateField = $notification::getDateFieldByStatus($status);

        return in_array($status, self::STATUSES_FOR_NOTIFICATIONS) && is_null($notification->{$dateField});
    }

    /**
     * Reserve an item based on the given notification ID.
     */
    public function reserveItem(int $notificationId): void
    {
        $inventoryNotification = $this->findNotification($notificationId);
        $inventoryNotification->orderItem->update(['status_key' => 'reserved']);
        $inventoryNotification->update(['reserved_at' => now()]);
    }

    /**
     * Collect an item based on the given notification ID.
     */
    public function collectItem(int $notificationId): void
    {
        $inventoryNotification = $this->findNotification($notificationId);
        $inventoryNotification->orderItem->update(['status_key' => 'collect']);
        $inventoryNotification->update(['collected_at' => now()]);
    }

    /**
     * Collect an item based on the given notification ID.
     */
    public function outOfStock(int $notificationId): void
    {
        $inventoryNotification = $this->findNotification($notificationId);
        $orderItem = $inventoryNotification->orderItem;
        $inventoryNotification->delete();
        $this->deductSizeFromInventory($orderItem);
    }

    /**
     * Update inventory based on the provided order items.
     *
     * @param  Collection<OrderItem>  $orderItems
     */
    public function updateInventory(Collection $orderItems): void
    {
        $this->setStocksPriority($orderItems);

        $orderItems->each(function (OrderItem $orderItem) {
            $this->deductSizeFromInventory($orderItem);
        });
    }

    /**
     * Deducts the size from the inventory after a purchase.
     */
    public function deductSizeFromInventory(OrderItem $orderItem, ?int $stockId = null): void
    {
        $totalAvailableCount = 0;
        $count = $orderItem->count;
        $singleNotification = $orderItem->inventoryNotification;
        $sizeField = AvailableSizes::convertSizeIdToField($orderItem->size_id);
        $totalQuery = implode('+', AvailableSizes::getSizeFields());

        $availableSizes = AvailableSizes::query()
            ->where('product_id', $orderItem->product_id)
            ->when($stockId, fn (Builder $query) => $query->where('stock_id', $stockId))
            ->where($sizeField, '>', 0)
            ->get(['id', 'stock_id', $sizeField, DB::raw("$totalQuery as total")])
            ->sortByDesc($this->getStocksPriority($sizeField));

        /** @var AvailableSizes */
        foreach ($availableSizes as $stock) {
            if ($count > $stock->{$sizeField}) {
                $count -= $stock->{$sizeField};
                $stock->{$sizeField} = 0;
            } else {
                $stock->{$sizeField} -= $count;
                $count = 0;
            }
            $stock->save();
            $totalAvailableCount += $stock->{$sizeField};

            if (empty($singleNotification)) {
                $singleNotification = $orderItem->inventoryNotification()->create([
                    'stock_id' => $stock->stock_id,
                ]);
                $this->handleChangeItemStatus($orderItem->refresh());
            }
        }

        if ($count === $orderItem->count) {
            $orderItem->outOfStock();
        }

        if ($totalAvailableCount <= 0) {
            $this->removeSizeFromCatalog($orderItem->product_id, $orderItem->size_id);
        }
    }

    /**
     * Set priority for stocks based on the provided order items.
     *
     * @param  Collection<OrderItem>  $orderItems
     */
    private function setStocksPriority(Collection $orderItems): void
    {
        if ($orderItems->count() === 1) {
            return;
        }

        $stockItemsQuery = AvailableSizes::query()->select(['product_id', 'stock_id']);
        $orderItems->each(function (OrderItem $orderItem) use ($stockItemsQuery) {
            $stockItemsQuery->orWhere(function (Builder $query) use ($orderItem) {
                $sizeField = AvailableSizes::convertSizeIdToField($orderItem->size_id);
                $query->where('product_id', $orderItem->product_id)->where($sizeField, '>', 0);
            });
        });

        $inventory = [];
        $stockItemsQuery->each(function (AvailableSizes $stockItem) use (&$inventory) {
            $inventory[$stockItem->product_id][$stockItem->stock_id] = 1;
        });

        $onlyInMinsk = false;
        foreach ($inventory as $stocks) {
            if (!isset($stocks[Stock::MINKS_ID])) {
                return;
            } elseif (count($stocks) === 1) {
                $onlyInMinsk = true;
            }
        }

        if ($onlyInMinsk) {
            $this->stocksPriority[Stock::MINKS_ID] = 2;
        }
    }

    /**
     * Get an array of closure for filtering stocks based on priority.
     */
    private function getStocksPriority(string $sizeField): array
    {
        return [
            function (AvailableSizes $stock1, AvailableSizes $stock2) {
                $stock1Priority = $this->stocksPriority[$stock1->stock_id] ?? 1;
                $stock2Priority = $this->stocksPriority[$stock2->stock_id] ?? 1;

                return $stock2Priority <=> $stock1Priority;
            },
            function (AvailableSizes $stock1, AvailableSizes $stock2) use ($sizeField) {
                return $stock2->{$sizeField} <=> $stock1->{$sizeField};
            },
            function (AvailableSizes $stock1, AvailableSizes $stock2) {
                return $stock2->total <=> $stock1->total;
            },
            function (AvailableSizes $stock1, AvailableSizes $stock2) {
                return $stock1->id <=> $stock2->id;
            },
        ];
    }

    /**
     *  Removes the size from the catalog for the specified product.
     */
    private function removeSizeFromCatalog(int $productId, int $sizeId): void
    {
        /** @var Product $product */
        if (empty($product = Product::find($productId, ['id']))) {
            return;
        }
        $product->sizes()->detach($sizeId);

        if ($product->sizes()->count() <= 0) {
            $product->delete();
        }
    }

    /**
     * Find OrderItemInventoryNotificationLog model by id
     */
    private function findNotification(int $id): OrderItemInventoryNotificationLog
    {
        return OrderItemInventoryNotificationLog::query()->find($id);
    }

    /**
     * Generate a pickup list for a specified private chat ID.
     */
    public function pickupList(int $privateChatId): string
    {
        /** @var Stock */
        $stock = Stock::query()->with('privateChat:id,chat_id')
            ->where('private_chat_id', $privateChatId)
            ->first(['id', 'name', 'address']);
        if (!$stock) {
            return "Чат с id {$privateChatId} не привязан ни к одному складу";
        }
        $pickupList = 'Забор на ' . date('d.m.Y') . ' магазин ' . $stock->name . ' ' . $stock->address;
        $orderItemIds = OrderItemInventoryNotificationLog::query()
            ->where('stock_id', $stock->id)
            ->whereNotNull('collected_at')
            ->whereNull('picked_up_at')
            ->whereNull('canceled_at')
            ->whereNull('completed_at')
            ->whereNull('returned_at')
            ->pluck('order_item_id');
        OrderItem::query()
            ->with('product')
            ->whereIn('id', $orderItemIds)
            ->where('status_key', 'collect')
            ->each(function (OrderItem $orderItem) use (&$pickupList) {
                $product = $orderItem->product;
                $size = $orderItem->size;
                $pickupList .= "\n - {$product->brand->name} {$product->sku} ({$product->id}), р. {$size->name}";
            });

        return $pickupList;
    }
}
