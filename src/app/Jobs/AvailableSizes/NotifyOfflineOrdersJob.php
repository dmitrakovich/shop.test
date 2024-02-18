<?php

namespace App\Jobs\AvailableSizes;

use App\Models\AvailableSizes;
use App\Models\Bots\Telegram\TelegramChat;
use App\Models\Logs\OrderItemStatusLog;
use App\Models\Orders\OrderItem;
use App\Models\Stock;
use App\Notifications\OrderItemInventoryNotification;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class NotifyOfflineOrdersJob extends AbstractAvailableSizesJob
{
    /**
     * Available sizes before updating
     */
    private array $oldStockItems;

    /**
     * Picked up available sizes
     */
    private array $movedStockItems;

    /**
     * Stock models collection
     *
     * @var Collection<Stock>
     */
    private Collection $stocks;

    /**
     * NotifyOfflineOrders constructor
     */
    public function __construct(private array $newStockItems)
    {
        $this->oldStockItems = AvailableSizes::get([
            'product_id', 'stock_id', ...AvailableSizes::getSizeFields(),
        ])->toArray();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $counter = 0;
        $emptySizes = $this->getEmptySizesStub();
        $this->setStocks();
        $this->setPreparedNewStockItems();
        $this->setPreparedMovedStockItems();

        foreach ($this->oldStockItems as $stockItem) {
            if (empty($productId = $stockItem['product_id'])) {
                continue;
            }
            $stockId = $stockItem['stock_id'];
            $newSizes = $this->newStockItems[$productId][$stockId] ?? $emptySizes;
            $oldSizes = Arr::only($stockItem, AvailableSizes::getSizeFields());

            foreach ($oldSizes as $sizeKey => $oldCount) {
                $newCount = $newSizes[$sizeKey];
                if ($this->shouldNotify($newCount, $oldCount, $productId, $stockId, $sizeKey)) {

                    $this->debug('try notify about:', compact('productId', 'stockId', 'sizeKey', 'oldCount', 'newCount'));

                    $this->notify($productId, $stockId, $sizeKey);
                    $counter++;
                }
            }
        }

        $this->log("Найдено $counter позиций на складе, купленных оффлайн");
    }

    /**
     * Get an array of prepared new stock items.
     */
    private function setPreparedNewStockItems(): void
    {
        $prepared = [];
        foreach ($this->newStockItems as $stockItem) {
            if (empty($stockItem['product_id'])) {
                continue;
            }
            $sizes = Arr::only($stockItem, AvailableSizes::getSizeFields());
            $prepared[$stockItem['product_id']][$stockItem['stock_id']] = $sizes;
        }

        $this->newStockItems = $prepared;
    }

    /**
     * Set up a prepared array of moved stock items for efficient processing.
     */
    private function setPreparedMovedStockItems(): void
    {
        OrderItemStatusLog::with(['orderItem'])
            ->has('orderItem')
            ->whereNotNull('picked_up_at')
            ->whereNull('moved_at')
            ->whereNull('completed_at')
            ->whereNull('returned_at')
            ->each(function (OrderItemStatusLog $movedItem) {
                $productId = $movedItem->orderItem->product_id;
                $sizeField = AvailableSizes::convertSizeIdToField($movedItem->orderItem->size_id);
                $count = $movedItem->orderItem->count;
                $stockId = $movedItem->stock_id;
                $this->movedStockItems[$productId][$stockId][$sizeField][$movedItem->id] = $count;
            });
    }

    /**
     * Get an array representing empty sizes stub.
     * This method generates an array with all available sizes set to zero.
     */
    private function getEmptySizesStub(): array
    {
        return array_map(fn () => 0, array_flip(AvailableSizes::getSizeFields()));
    }

    /**
     * Determine if a notification should be sent based on inventory count changes.
     */
    private function shouldNotify(int $newCount, int $oldCount, int $productId, int $stockId, string $sizeKey): bool
    {
        if ($newCount >= $oldCount) {
            return false;
        }
        $movedItems = $this->movedStockItems[$productId][$stockId][$sizeKey] ?? [];
        foreach ($movedItems as $id => $count) {
            $newCount += $count;
            OrderItemStatusLog::where('id', $id)->update(['moved_at' => now()]);
            if ($newCount >= $oldCount) {
                return false;
            }
        }

        return true;
    }

    /**
     * Notify the Telegram chat about a offline order.
     */
    private function notify(int $productId, int $stockId, string $sizeField): void
    {
        $chat = $this->getChatByStockId($stockId);
        if (empty($chat) || $this->areStockNotificationsPaused($stockId)) {
            return;
        }

        $notification = new OrderItemStatusLog(['stock_id' => $stockId]);
        $orderItem = (new OrderItem([
            'product_id' => $productId,
            'size_id' => AvailableSizes::convertFieldToSizeId($sizeField),
            'status_key' => 'complete',
        ]))->setRelation('inventoryNotification', $notification);

        $chat->notifyNow(new OrderItemInventoryNotification($orderItem));
    }

    /**
     * Set the stocks for the current context.
     */
    private function setStocks(): void
    {
        $this->stocks = Stock::with(['groupChat'])
            ->where('check_availability', true)
            ->get(['id', 'group_chat_id', 'offline_notifications_pause_until'])
            ->keyBy('id');
    }

    /**
     * Get the Telegram chat model associated with the specified stock ID.
     */
    private function getChatByStockId(int $stockId): ?TelegramChat
    {
        return $this->stocks[$stockId]->groupChat;
    }

    /**
     * Check if offline notifications are paused for a specific stock.
     */
    private function areStockNotificationsPaused(int $stockId): bool
    {
        return $this->stocks[$stockId]->areOfflineNotificationsPaused();
    }
}
