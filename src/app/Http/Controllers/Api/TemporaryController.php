<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Shop\CatalogController;
use App\Http\Requests\FilterRequest;
use Illuminate\Http\JsonResponse;

class TemporaryController extends Controller
{
    /**
     * @todo сделать общий для каталога и товара метод
     * @todo и придумать ему понятное название
     */
    public function catalog(FilterRequest $request): JsonResponse
    {
        $view = app(CatalogController::class)->show($request);

        return response()->json([
            'is_catalog' => true,
            'data' => $view->getData(),
        ]);
    }
}
