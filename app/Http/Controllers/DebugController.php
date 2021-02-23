<?php

namespace App\Http\Controllers;

use App\Mail\OrderCreated;
use App\Models\Order;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class DebugController extends Controller
{
    public function index()
    {


        // php artisan make:mail OrderShipped

        $email = 'dmitrakovich.andrey@yandex.by';
        $order = Order::with('data')->find(18);

        // dd(
        //     $order,
        //     $order->getMaxItemsPrice(),
        // );



        Mail::to($email)->send(new OrderCreated($order));


        return view('emails.order-created', compact('order'));



        dd(324543);

        throw new Exception("Test exeption");

        list($a, $b, $c) = [1, 2];
        dd($a, $b, $c);
        dd(Auth::id());
        dd(Auth::user());
    }
}
