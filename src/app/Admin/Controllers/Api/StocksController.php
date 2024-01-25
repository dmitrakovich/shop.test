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
            ->pluck('stock_id')
            ->toArray();

        if ($orderItemId = $request->input('orderItemId')) {
            $orderItem = OrderItem::find($orderItemId);
            if ($orderItem && $orderItem->size_id === $sizeId) {
                if ($currentStockId = $orderItem->inventoryNotification?->stock_id) {
                    $stockIds[] = $currentStockId;
                }
            }
        }

        return Stock::whereIn('id', $stockIds)
            ->get(['id', 'internal_name as text'])
            ->each(fn (Stock $stock) => $stock->setAppends([]))
            ->toArray();
    }
}
