<?php

namespace App\Http\Controllers;

use App\Models\Orders\Order;
use App\Models\Product;
use App\Models\User\User;
use App\Notifications\TestSms;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use DefStudio\Telegraph\Models\TelegraphChat;

use Illuminate\Database\Eloquent\Model;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

class DebugController extends Controller
{
    public function index()
    {
        return 'ok';


        // $bot = \DefStudio\Telegraph\Models\TelegraphBot::find(2);
        /** @var TelegraphChat $chat */
        $chat = TelegraphChat::find(4);



        /** @var Product $product */
        $product = Product::query()->latest()->first();


        $msg = <<<MSG
        <b>заголовок действия (смотри ниже)</b>
        {$product->brand->name} {$product->sku} размер
        магазин где находится товар
        MSG;


        $keyboard = Keyboard::make()->row([
            Button::make('✅ Отложено')->action('like')->param('id', '41'),
            Button::make('❌ Нет в наличии')->action('dislike')->param('id', '42'),
        ]);

        $response = $chat->message($msg)->photo($product->getFirstMediaPath())->keyboard($keyboard)->send();

        // $chat->deleteKeyboard(14)->send();
        // return 'deleteKeyboard';

        $response->dd();

        /** @var User $user */
        $user = User::findOrFail('xxxx');
        dd($user, $user->notifyNow(new TestSms()));

        // php artisan make:mail OrderShipped

        return dd(Order::with('data')->find(3));
    }

    /**
     * Show php info
     */
    public function phpinfo(): never
    {
        exit(phpinfo());
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
