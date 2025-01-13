<?php

namespace App\Http\Controllers\Api;

use App\Facades\Cart;
use App\Http\Controllers\Controller;
use App\Models\Favorite;
use Illuminate\Http\JsonResponse;

class AppController extends Controller
{
    public function init(): JsonResponse
    {
        $cart = Cart::getCart();
        $favorites = Favorite::query()->with('product')->get();

        return response()->json([
            'cart' => [
                'total_count' => $cart->itemsCount(),
                'total_sum' => $cart->getTotalPrice(),
            ],
            'favorites' => [
                'total_count' => $favorites->count(),
                'total_sum' => $favorites->sum('product.price'),
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
}
