<?php

namespace App\Admin\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AvailableSizes;
use App\Models\Orders\OrderItem;
use App\Models\Stock;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class StocksController extends Controller
{
    /**
     * Return stocks data
     *
     * @throws ModelNotFoundException
     */
    public function get(Request $request): array
    {
        $productId = $request->integer('productId');
        $sizeId = $request->integer('sizeId');
        $sizeField = AvailableSizes::convertSizeIdToField($sizeId);

        $stockIds = empty($sizeField) ? [] : AvailableSizes::query()
            ->where('product_id', $productId)
            ->where($sizeField, '>', 0)
            ->whereHas('stock', fn ($query) => $query->active())
            ->pluck('stock_id')
            ->toArray();

        $currentStockId = null;
        if ($orderItemId = $request->input('orderItemId')) {
            /** @var OrderItem|null $orderItem */
            $orderItem = OrderItem::query()->find($orderItemId);
            if ($orderItem && $orderItem->size_id === $sizeId) {
                if ($currentStockId = $orderItem->inventoryNotification?->stock_id) {
                    $stockIds[] = $currentStockId;
                }
            }
        }

        return Stock::query()
            ->whereIn('id', $stockIds)
            ->where(fn ($query) => $query->active()->when(
                $currentStockId,
                fn ($query) => $query->orWhere('id', $currentStockId)
            ))
            ->get(['id', 'internal_name as text'])
            ->each(fn (Stock $stock) => $stock->setAppends([]))
            ->toArray();
    }
}
