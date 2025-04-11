<?php

namespace App\Http\Controllers;

use App\Admin\Models\Product;
use App\Jobs\OneC\UpdateOfflineOrdersJob;
use App\Models\Orders\OfflineOrder;
use App\Models\Orders\Order;
use App\Models\User\User;
use App\Services\InstagramService;
use App\Services\Order\OrderItemInventoryService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

class DebugController extends Controller
{
    public function index(InstagramService $instagramService)
    {
        throw new \Exception('test sentry action');


        dd(
            $instagramService->getPosts()
        );

        // Mail::raw('test email sending', function ($message) {
        //     $message->to('dmitrakovich.andrey@yandex.by');
        // });

        // (new OrderItemInventoryService)->outOfStock(1426);

        // (new UpdateOfflineOrdersJob)->notify(OfflineOrder::query()->find(26167));

        return 'ok';
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
    protected function formatPhone($model, string $phoneFieldName = 'phone', string $countryCode = 'BY')
    {
        try {
            $phone = $model->$phoneFieldName;
            $phoneUtil = PhoneNumberUtil::getInstance();

            if (empty($phone) || strlen($phone) < 6) {
                return;
            }

            $parsedPhone = $phoneUtil->parse($phone, $countryCode);

            if (!$phoneUtil->isValidNumber($parsedPhone)) {
                return;
            }

            $formattedPhone = $phoneUtil->format($parsedPhone, PhoneNumberFormat::E164);

            $model->$phoneFieldName = $formattedPhone;

            if ($model->isDirty()) {
                echo "Old phone: '$phone', new phone: '$formattedPhone'<br>";
                $model->save();
            }
        } catch (\Throwable $th) {
            dump($th->getMessage());

            return;
        }
    }
}
