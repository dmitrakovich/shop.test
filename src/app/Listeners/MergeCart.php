<?php

namespace App\Listeners;

use App\Models\Cart;
use App\Models\CartData;
use Illuminate\Support\Facades\Cookie;

class MergeCart
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        // @todo Will finish after technical task

        /*$cartTokenInCookie = Cookie::get('cart_token');

        if (empty($cartTokenInCookie)) {
            return;
        }

        $authUserCart = Cart::find($event->user->cart_token);

        if (empty($authUserCart)) {
            $authUserCart = (new Cart())->createIfNotExists();
        }

        // auth 0, cookie 0
        // auth 1, cookie 0
        // auth 0, cookie 1
        // auth 1, cookie 1
        // not created cart for user
        // not created cart for cookie (return)

        dd($authUserCart);

        // CartData::where('cart_id', $cartTokenInCookie)
            // ->update(['cart_id', $])*/
    }
}
