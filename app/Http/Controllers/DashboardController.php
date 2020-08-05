<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserDataUpdateRequest;
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
        $orders = [
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
        ];
        $orders = array_merge($orders, $orders);
        $orders = array_merge($orders, $orders);
        return view('dashboard.orders', compact('orders'));
    }
    /**
     * Получить данные профиля
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function getProfileData()
    {
        $user = auth()->user();
        $countriesList = [
            'Беларусь',
            'Украина',
            'Российская Федерация',
            'Казахстан'
        ];
        return view('dashboard.profile', compact('user', 'countriesList'));
    }

    /**
     * Получить данные профиля
     *
     * @param User $user
     * @param Request $request
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
     */
    public function updateProfileData(User $user, UserDataUpdateRequest $request)
    {
        $result = $user->update($request->input());
        if ($result) {
            return redirect()
                ->route('dashboard-profile')
                ->with(['success' => 'Данные успешно обновлены']);
        } else {
            return back()
                ->withErrors(['msg' => 'Ошибка сохранения'])
                ->withInput();
        }
    }
}
