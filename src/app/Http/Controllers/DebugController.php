<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Mail\OrderCreated;
use App\Models\Logs\SmsLog;
use App\Models\Orders\Order;
use App\Notifications\TestSms;
use App\Services\LogService;
use Encore\Admin\Auth\Database\Administrator;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;
use Illuminate\Database\Eloquent\Model;

class DebugController extends Controller
{
    public function index(LogService $logService)
    {
        /** @var User $user */
        // $user = User::find('xxx');
        // dd($user, $user->notifyNow(new TestSms()));

        $logService->logSms(
            '375333467338',
            'sms text',
            'viber(60)-sms',
            Administrator::inRandomOrder()->value('id'),
            Order::inRandomOrder()->value('id'),
            'in queue'
        );

        $sms = SmsLog::latest()->first();
        dd($sms->toArray());

        // php artisan make:mail OrderShipped

        return $this->printOrder(Order::with('data')->find(3));
    }

    /**
     * Show php info
     */
    public function phpinfo(): never
    {
        exit(phpinfo());
    }

    /**
     * @param Order $order
     * @return void
     */
    public function printOrder(Order $order)
    {
        // TODO: create route if needed

        // $email = 'dmitrakovich.andrey@yandex.by';
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

            if (empty($phone) || strlen($phone) < 6) {
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
            dump($th->getMessage());
            return;
        }
    }
}
