<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Mail\OrderCreated;
use App\Models\Orders\Order;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;
use Illuminate\Database\Eloquent\Model;

class DebugController extends Controller
{
    public function index()
    {
        $this->formatPhones();
        dd(0000);

        // php artisan make:mail OrderShipped

        $email = 'dmitrakovich.andrey@yandex.by';
        $order = Order::with('data')->find(62);

        // Mail::to($email)->send(new OrderCreated($order));

        return view('emails.order-created', compact('order'));
    }

    /**
     * @return void
     */
    public function formatPhones()
    {
        Order::all()->each(function (Model $order) {
            $this->formatPhone($order);
        });


        User::all()->each(function (Model $user) {
            $this->formatPhone($user);
        });
    }

    /**
     * @return void
     */
    protected function formatPhone($model, string $phoneFileldName = 'phone', string $countryCode = 'BY')
    {
        try {
            $phone = $model->$phoneFileldName;
            $phoneUtil = PhoneNumberUtil::getInstance();

            if (empty($phone)) {
                return;
            }

            $parsedPhone = $phoneUtil->parse($phone, $countryCode);

            if (!$phoneUtil->isValidNumber($parsedPhone)) {
                return;
            }

            $formatedPhone = $phoneUtil->format($parsedPhone, PhoneNumberFormat::E164);

            $model->$phoneFileldName = $formatedPhone;

            if ($model->isDirty()) {
                echo "Old phone: '$phone', new phone: '$formatedPhone'<br>";
                $model->save();
            }
        } catch (\Throwable $th) {
            dd($th);
        }
    }
}
