<?php

namespace App\Admin\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class ProductController extends Controller
{
    /**
     * Return paginated products by id
     *
     * @param Request $request
     * @return LengthAwarePaginator
     */
    public function getById(Request $request): LengthAwarePaginator
    {
        $id = $request->get('q');

        return Product::where('id', 'like', "%$id%")->paginate(null, ['id', 'id as text']);
    }

    /**
     * Return product data
     *
     * @param Request $request
     * @return array
     */
    public function getProductDataById(Request $request): array
    {
        $product = $this->getProduct($request);

        return [
            'name' => $product->extendedName(),
            'link' => $product->getUrl(),
            'image' => $product->getFirstMediaUrl()
        ];
    }

    /**
     * Return product sizes [id => text]
     *
     * @param Request $request
     * @return EloquentCollection
     */
    public function sizesByProductId(Request $request): EloquentCollection
    {
        return $this->getProduct($request)->sizes()->get(['id', 'name as text']);
    }

    /**
     * Get Product model from request
     *
     * @param Request $request
     * @throws ModelNotFoundException
     * @return Product
     */
    protected function getProduct(Request $request): Product
    {
        $productId = $request->get('q');

        return Product::findOrFail($productId);
    }
}
