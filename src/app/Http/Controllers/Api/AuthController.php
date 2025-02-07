<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SendOtpRequest;
use App\Services\AuthService;

class AuthController extends Controller
{
    public function sendOtp(SendOtpRequest $request, AuthService $authService): void
    {
        $user = $authService->getOrCreateUser($request->input('phone'));

        $authService->generateNewOTP($user);
    }

    public function attempt(/*ValidateOtpRequest $request,*/ AuthService $authService) // : UserResource
    {
        // * валидацию opt производить в request

        // if (!$authService->validateOTP($this->input('otp'))) {
        //     RateLimiter::hit($this->throttleKeyForOTP());

        //     $this->returnBack(['otp' => __('auth.otp_failed')]);
        // }

        // $user->updatePhoneVerifiedAt();
        // Auth::login($user, true);

        // return $user;
    }
}
