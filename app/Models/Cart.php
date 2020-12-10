<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class Cart extends Model
{
    use HasFactory;

    public function setCart()
    {


        /*dd(
            Auth::user()
        );

        $cartId = User::IsAuth() ?  null;


        if (User::IsAuth()) {
            $cart_id = User::get()->cart_id;
        } else if (Cookie::has('cart_token')) {
            $cart_id = Cookie::get('cart_token');
        }
        return Cart::where('id', $cart_id)->first() ?? new Cart();*/
        return new Cart();
    }

    public function data()
    {
        $this->hasMany(CartData::class);
    }

    public function itemsCount()
    {
        return 5;
        // return count($this->items);
    }
}
