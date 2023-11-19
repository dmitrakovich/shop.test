<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\SyncRequest;
use App\Models\User\User;
use App\Services\OldSiteSyncService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.register', ['user' => Auth::user()]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    // public function store(RegisterRequest $request)
    // {
    //     $user = User::forceCreate([
    //         'first_name' => $request->first_name,
    //         'email' => $request->email,
    //         'password' => Hash::make($request->password),
    //     ]);

    //     event(new Registered($user));

    //     Auth::login($user);

    //     return redirect()->route('verification.notice');
    // }

    /**
     * Sync users with another DB
     *
     * @param  SyncRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sync(SyncRequest $syncRequest)
    {
        $oldId = (int)$syncRequest->input('id');
        $userData = $syncRequest->validated();

        $existUser = User::when(!empty($userData['phone']), function ($query) use ($userData) {
            $query->where('phone', $userData['phone']);
        })->when(!empty($userData['email']), function ($query) use ($userData) {
            $query->orWhere('email', $userData['email']);
        })->first();

        try {
            if ($user = $existUser) {
                $user->usergroup_id = $userData['usergroup_id'];
                $user->email ??= $userData['email'];

                if ($user->hasAddresses()) {
                    $user->getFirstAddress()->update($userData);
                } else {
                    $user->addresses()->create($userData);
                }
                $user->save();
            } else {
                $fillable = (new User)->mergeFillable(['password', 'remember_token']);
                $user = User::forceCreate(Arr::only($userData, $fillable->getFillable()));
                $user->addresses()->create($userData);
            }
        } catch (\Throwable $th) {
            \Sentry\captureException($th);
            abort(OldSiteSyncService::errorResponse($th->getMessage()));
        }

        return OldSiteSyncService::successResponse([$oldId => $user->id]);
    }
}
