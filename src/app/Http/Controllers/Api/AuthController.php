<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginAttemptRequest;
use App\Http\Requests\Auth\SendOtpRequest;
use App\Http\Resources\User\UserResource;
use App\Services\AuthService;
use Illuminate\Auth\Events\Login;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $authService) {}

    public function sendOtp(SendOtpRequest $request): void
    {
        $user = $this->authService->getOrCreateUser($request->input('phone'));

        $this->authService->generateNewOTP($user);
    }

    public function attempt(LoginAttemptRequest $request): array
    {
        event(new Login('api', $request->user(), false));

        return [
            'user' => new UserResource($request->user()),
            'token' => $this->authService->regenerateToken($request->user()),
        ];
    }
}
