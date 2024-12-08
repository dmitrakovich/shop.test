<?php

namespace App\Http\Controllers\Api;

use App\Facades\Cart;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Shop\CatalogController;
use App\Http\Controllers\Shop\ProductController;
use App\Http\Requests\FilterRequest;
use App\Models\Favorite;
use App\Models\Product;
use App\Models\Url;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class TemporaryController extends Controller
{
    public function appInit(): JsonResponse
    {
        // $cart = Cart::getCart();

        return response()->json([
            'cart' => [
                'total_count' => 4, // $cart->itemsCount(),
                'total_sum' => 138, // $cart->getTotalPrice(),
            ],
            'favorites' => [
                'total_count' => Favorite::query()->count(),
                'total_sum' => 0,
            ],
            'contacts' => [
                'phone_main' => '+375 (29) 179-37-90',
                'phone_by' => '+375 (29) 522-77-22',
                'phone_ru' => '8-800-100-77-69',
                'email' => config('contacts.email.link'),
                'telegram' => config('contacts.telegram.link'),
                'telegram_channel' => config('contacts.telegram-channel.link'),
                'viber' => config('contacts.viber.link'),
                'viber_channel' => config('contacts.viber-channel.link'),
                'whats_app' => config('contacts.whats-app.link'),
                'instagram' => config('contacts.instagram.link'),
            ],
            'work_time' => 'Ежедневно с 08:00 до 21:00',
        ]);
    }

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
