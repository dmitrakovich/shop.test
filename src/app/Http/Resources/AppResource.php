<?php

namespace App\Http\Resources;

use App\Facades\Cart;
use App\Facades\Currency;
use App\Facades\Favorite;
use App\Http\Resources\Cart\CartResource;
use App\Http\Resources\Favorite\FavoritesResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppResource extends JsonResource
{
    /**
     * Create a new resource instance.
     */
    public function __construct() {}

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'cart' => new CartResource(Cart::getCart()),
            'favorites' => new FavoritesResource(Favorite::getProducts()),
            'currency' => Currency::getCurrentCurrency(),
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
            'work_time' => config('shop.work_time'),
        ];
    }
}
