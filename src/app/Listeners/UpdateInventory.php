<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Models\AvailableSizes;
use App\Models\Orders\OrderItem;
use App\Models\Product;
use App\Services\Order\OrderItemInventoryService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateInventory implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct(private OrderItemInventoryService $orderItemInventoryService)
    {
    }

    /**
     * Handle the event.
     */
    public function handle(OrderCreated $event): void
    {
        $event->order->items->each(function (OrderItem $orderItem) {
            $this->deductSizeFromInventory(
                $orderItem,
                $orderItem->product_id,
                $orderItem->size_id,
                $orderItem->count
            );
        });
    }

    /**
     * Deducts the size from the inventory after a purchase.
     */
    private function deductSizeFromInventory(OrderItem $orderItem, int $productId, int $sizeId, int $count): void
    {
        $totalAvailableCount = 0;
        $singleNotification = $orderItem->invertoryNotification;
        $sizeField = AvailableSizes::convertSizeIdToField($sizeId);

        $availableSizes = AvailableSizes::query()
            ->with(['stock:id'])
            ->where('product_id', $productId)
            ->where($sizeField, '>', 0)
            ->orderBy($sizeField, 'desc')
            ->get(['id', 'stock_id', $sizeField])
            ->sortBy(fn (AvailableSizes $stock) => $stock->stock_id === 17); // Minsk to the end

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
                $singleNotification = $orderItem->invertoryNotification()->create([
                    'stock_id' => $stock->stock_id
                ]);
                $this->orderItemInventoryService->handleChangeItemStatus($orderItem->refresh());
            }
        }

        if ($totalAvailableCount <= 0) {
            $this->removeSizeFromCatalog($productId, $sizeId);
        }
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
}
