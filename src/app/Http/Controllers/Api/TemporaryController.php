<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Shop\CatalogController;
use App\Http\Controllers\Shop\ProductController;
use App\Http\Requests\FilterRequest;
use App\Models\Product;
use App\Models\Url;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class TemporaryController extends Controller
{
    /**
     * @todo сделать общий для каталога и товара метод
     * @todo и придумать ему понятное название
     */
    public function catalog(FilterRequest $request): JsonResponse
    {
        $path = $request->route('path');
        $slug = (string)Str::of($path)->explode('/')->last();
        $url = Url::search($slug);

        $isCatalog = !($url && $url['model_type'] === Product::class);

        $view = $isCatalog
            ? app(CatalogController::class)->show($request)
            : app(ProductController::class)->show($url->model_id);

        return response()->json([
            'is_catalog' => $isCatalog,
            'data' => $view->getData(),
        ]);
    }
}
