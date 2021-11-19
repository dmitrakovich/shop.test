<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\UserDataUpdateRequest;
use App\Models\Country;
use Scriptixru\SypexGeo\SypexGeoFacade as SxGeo;

class DashboardController extends Controller
{
    /**
     * Show the form for editing user profile
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(Request $request)
    {
        /** @var \App\Models\User */
        $user = auth()->user();
        $countries = Country::get(['id', 'name', 'code', 'prefix']);

        return view('dashboard.profile', [
            'user' => auth()->user(),
            'emailVerified' => $request->has('verified'),
            'userCountryId' => $user->getFirstAddress()->country_id
                ?? $countries->where('code', SxGeo::getCountry())->first()->id,
            'countriesList' => $countries->pluck('name', 'id')
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
        $validatedData = $request->validated();
        $result = $user->update($validatedData);

        if ($user->hasAddresses()) {
            $user->getFirstAddress()->update($validatedData);
        } else {
            $user->addresses()->create($validatedData);
        }

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
