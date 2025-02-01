<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\UpdateRequest;
use App\Models\Country;
use App\Models\User\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
            'countries' => Country::getAll(),
            'currentCountry' => Country::getCurrent(),
        ]);
    }

    /**
     * Получить данные профиля
     *
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
     */
    public function update(User $user, UpdateRequest $request)
    {
        $validatedData = $request->validated();
        $result = $user->update($validatedData);

        if ($user->hasAddresses()) {
            $user->getFirstAddress()?->update($validatedData);
        } else {
            $user->addresses()->create($validatedData);
        }

        // if ($request->filled('password')) {
        //     $request->validate(['password' => ['required', 'string', 'min:8']]);

        //     $user->forceFill([
        //         'password' => Hash::make($request->input('password')),
        //         'remember_token' => Str::random(60),
        //     ])->save();
        // }

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
