<?php

namespace App\Admin\Controllers\Api;


use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductController extends Controller
{
    /**
     * Return products for select by id
     *
     * @param Request $request
     * @return LengthAwarePaginator
     */
    public function getById(Request $request): LengthAwarePaginator
    {
        $id = $request->get('q');

        return Product::where('id', 'like', "%$id%")->paginate(null, ['id', 'id as text']);
    }

    public function getProductNameById(Request $request)
    {
        $product = $this->getProduct($request);

        return [
            'text' => $product->extendedName()
        ];
    }

    /**
     * Undocumented function
     *
     * @param Request $request
     * @return EloquentCollection
     */
    public function sizesByProductId(Request $request): EloquentCollection
    {
        $product = $this->getProduct($request);

        return $product->sizes()->get(['id', 'name as text']);
    }





    protected function getProduct(Request $request)
    {
        $productId = $request->get('q');

        return Product::findOrFail($productId);
    }
}
