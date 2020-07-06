<?php

namespace App\Http\Controllers;

use App\User;
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
        $data = [
            'user' => auth()->user(),
            'countriesList' => [
                'Беларусь',
                'Украина',
                'Российская Федерация',
                'Казахстан'
            ]
        ];
        return view('dashboard.profile', $data);
    }

    /**
     * Получить данные профиля
     *
     * @param User $user
     * @param Request $request
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
     */
    public function updateProfileData(User $user, Request $request)
    {
        $validatedData = $request->validate([
            'last_name' => ['max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'patronymic_name' => ['max:255'],
            'email' => ['email:filter', 'unique:users,email,'.$user->id],
            'phone' => [],
            'birth_date' => ['date', 'nullable'],
            'country' => ['integer'],
            'address' => [],
        ]);
        $user->fill($validatedData);
        $user->save();
        return back();
    }
}
