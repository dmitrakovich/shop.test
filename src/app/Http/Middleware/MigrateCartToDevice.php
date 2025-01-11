<?php

namespace App\Http\Middleware;

use App\Enums\Cookie as CookieEnum;
use App\Facades\Device;
use App\Models\Cart;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class MigrateCartToDevice
{
    /**
     * Handle an incoming request.
     *
     * @todo remove after 01.07.2025
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $cartId = $request->cookie(CookieEnum::OLD_CART_ID->value);
        if ($cartId) {
            $this->attachCartToCurrentDevice($cartId);
            Cookie::queue(Cookie::forget(CookieEnum::OLD_CART_ID->value));
        }

        return $next($request);
    }

    /**
     * Attaches a cart to the current device.
     *
     * If the current device already has a cart with items, no changes are made.
     * If the current device has an empty cart, it is deleted first.
     * The specified cart is then updated to belong to the current device.
     *
     * @param  int  $cartId  The ID of the cart to attach
     */
    private function attachCartToCurrentDevice(int $cartId): void
    {
        if (Device::current()->cart?->items->isNotEmpty()) {
            return;
        }
        if (Device::current()->cart) {
            Device::current()->cart->delete();
        }

        Cart::query()->where('id', $cartId)->update([
            'device_id' => Device::id(),
        ]);

        Device::current()->load('cart');
    }
}
