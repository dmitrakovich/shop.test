<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginAttemptRequest;
use App\Http\Requests\Auth\SendOtpRequest;
use App\Http\Resources\User\UserResource;
use App\Services\AuthService;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $authService) {}

    /**
     * Sends a one-time password (OTP) to the user's phone number
     */
    public function sendOtp(SendOtpRequest $request): void
    {
        $user = $this->authService->getOrCreateUser($request->input('phone'));

        $this->authService->generateNewOTP($user);
    }

    /**
     * Attempts to authenticate a user and returns their data with a new token
     */
    public function attempt(LoginAttemptRequest $request): array
    {
        event(new Login('api', $request->user(), false));

        return [
            'user' => new UserResource($request->user()),
            'token' => $this->authService->regenerateToken($request->user()),
        ];
    }

    /**
     * Revokes the current user's token
     */
    public function logout(Request $request): void
    {
        $this->authService->revokeToken($request->user());
    }
}
