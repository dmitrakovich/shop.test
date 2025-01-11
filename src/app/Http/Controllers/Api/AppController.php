<?php

namespace App\Http\Controllers\Api;

use App\Facades\Cart;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class AppController extends Controller
{
    public function init(): JsonResponse
    {
        // $cart = Cart::getCart();

        return response()->json([
            'cart' => [
                'total_count' => 4, // $cart->itemsCount(),
                'total_sum' => 138, // $cart->getTotalPrice(),
            ],
            'favorites' => [
                'total_count' => 2, // Favorite::query()->count(),
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
}
