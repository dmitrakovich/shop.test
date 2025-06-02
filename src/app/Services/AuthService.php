<?php

namespace App\Services;

use App\Events\Analytics\Registered;
use App\Models\User\User;
use App\Notifications\VerificationPhoneSms;

/**
 * Class AuthService
 */
class AuthService
{
    /**
     * AuthService constructor.
     */
    public function __construct(private User $user) {}

    /**
     * Find user or create new by phone number
     */
    public function findOrCreateUser(string $phone): User
    {
        $user = app(UserService::class)->findOrCreateByPhone($phone);

        if ($user->wasRecentlyCreated) {
            event(new Registered($user));
        }

        return $user;
    }

    /**
     * Generate, send & save new one-time password
     */
    public function generateNewOTP(User $user): void
    {
        $user->notify(
            new VerificationPhoneSms($user->generateNewOtp())
        );
    }

    /**
     * Validate one-time password
     */
    public function validateOtp(User $user, ?string $enteredOtp): bool
    {
        return $user->validateOtp($enteredOtp);
    }

    /**
     * Regenerate API token for user.
     */
    public function regenerateToken(User $user): string
    {
        return $user->createToken('api', expiresAt: now()->addYear())->plainTextToken;
    }

    /**
     * Revoke current API token for user
     */
    public function revokeToken(User $user): void
    {
        $user->currentAccessToken()->delete();
    }
}
