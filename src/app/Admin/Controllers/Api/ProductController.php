<?php

namespace App\Admin\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Orders\OrderItem;
use App\Models\Product;
use App\Models\Size;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Return paginated products by id
     */
    public function getById(Request $request): LengthAwarePaginator
    {
        $id = $request->get('q');

        return Product::where('id', 'like', "%$id%")->paginate(null, ['id', 'id as text']);
    }

    /**
     * Return product data
     *
     * @throws ModelNotFoundException
     */
    public function getProductDataById(Request $request): array
    {
        /** @var Product */
        $product = Product::withTrashed()->findOrFail($request->input('productId'));
        $sizes = $product->sizes()->get(['id', 'name as text'])->keyBy('id')->toArray();

        if ($orderItemId = $request->input('orderItemId')) {
            /** @var OrderItem $orderItem */
            $orderItem = OrderItem::findOrFail($orderItemId);
            if (!isset($sizes[$orderItem->size_id])) {
                /** @var Size $size */
                $size = Size::findOrFail($orderItem->size_id);
                $sizes[$size->id] = ['id' => $size->id, 'text' => $size->name];
                ksort($sizes);
            }
        }

        return [
            'name' => $product->extendedName(),
            'link' => $product->getUrl(),
            'image' => $product->getFirstMediaUrl(),
            'sizes' => array_values($sizes),
        ];
    }
}
