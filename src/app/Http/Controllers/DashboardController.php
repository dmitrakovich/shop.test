<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserDataUpdateRequest;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
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
