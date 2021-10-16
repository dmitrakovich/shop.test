<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\UserDataUpdateRequest;

class DashboardController extends Controller
{
    /**
     * Show the form for editing user profile
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(Request $request)
    {
        return view('dashboard.profile', [
            'user' => auth()->user(),
            'emailVerified' => $request->has('verified'),
            'countriesList' => [
                'Беларусь',
                'Украина',
                'Российская Федерация',
                'Казахстан'
            ]
        ]);
    }

    /**
     * Получить данные профиля
     *
     * @param User $user
     * @param Request $request
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
     */
    public function update(User $user, UserDataUpdateRequest $request)
    {
        $result = $user->update($request->input());

        if ($request->filled('password')) {
            $request->validate(['password' => ['required', 'string', 'min:8']]);

            $user->forceFill([
                'password' => Hash::make($request->input('password')),
                'remember_token' => Str::random(60),
            ])->save();
        }

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
