<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\GetOtpRequest;
use App\Services\AuthService;

class AuthController extends Controller
{
    public function sendOtp(GetOtpRequest $request, AuthService $authService): void
    {
        $user = $authService->getOrCreateUser($request->input('phone'));

        $authService->generateNewOTP($user);
    }
}
