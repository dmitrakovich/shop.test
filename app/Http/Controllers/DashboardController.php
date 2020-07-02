<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Получить данные о заказах заказы
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function getOrders()
    {
        $data = [
            'orders' => [
                (object)[
                    'id' => '2',
                    'number' => 'BY3567540987',
                    'date' => 'от 29 апреля 2020',
                    'price' => '137,60 BYN',
                    'address' => 'ул. Буденного 17 - 26 Брест',
                    'status' => 'Ожидает подтверждения менеджером',
                    'photos' => [
                        '/images/temp/order-product-photo_temp.png',
                        '/images/temp/order-product-photo_temp.png',
                    ]
                ],
                (object)[
                    'id' => '1',
                    'number' => 'BY3567540923',
                    'date' => 'от 3 марта 2020',
                    'price' => '137,60 BYN',
                    'address' => 'ул. Буденного 17 - 26 Брест',
                    'status' => 'Доставлен',
                    'photos' => [
                        '/images/temp/order-product-photo_temp2.png',
                    ]
                ],
            ] 
        ];
        $data['orders'] = array_merge($data['orders'], $data['orders']);
        $data['orders'] = array_merge($data['orders'], $data['orders']);
        return view('dashboard.orders', $data);
    }
    /**
     * Получить данные профиля
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function getProfileData()
    {
        return view('dashboard.profile', ['user' => auth()->user()]);
    }

    /**
     * Получить данные профиля
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function updateProfileData()
    {
        return '34564545345345';
        // return view('dashboard.profile', ['user' => auth()->user()]);
    }
}
