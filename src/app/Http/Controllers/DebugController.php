<?php

namespace App\Http\Controllers;

use App\Jobs\Ssh\CreateTunnelJob;
use App\Jobs\Ssh\DestroyTunnelJob;
use App\Models\OneC\OfflineOrder;
use App\Models\Orders\Order;
use App\Models\User\User;
use App\Services\Order\OrderItemInventoryService;
use Illuminate\Database\Eloquent\Model;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

class DebugController extends Controller
{
    public function index()
    {
        // (new OrderItemInventoryService)->outOfStock(1426);

        // $offlineOrders = OfflineOrder::query()->get();

        // dd($offlineOrders);

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
