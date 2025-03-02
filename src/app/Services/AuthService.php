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
    public function getOrCreateUser(
        string $phone,
        array $userData = [],
        array $userAddress = []
    ): User {
        $user = $this->user->getByPhone($phone) ?? $this->user->query()->create([
            'phone' => $phone,
            ...$userData,
        ]);

        // !!!!
        if (!empty($userAddress)) {
            $user->load('lastAddress');
            if ($user->lastAddress) {
                $user->lastAddress->fill($userAddress);
                if ($user->lastAddress->isDirty()) {
                    $user->lastAddress->approve = false;
                    $user->lastAddress->save();
                }
            } else {
                $user->addresses()->create($userAddress);
            }
        }

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
     *
     * Deletes all existing tokens and creates a new one.
     */
    public function regenerateToken(User $user): string
    {
        /** @var \Illuminate\Database\Eloquent\Relations\MorphMany $tokens */
        $tokens = $user->tokens();
        $tokens->delete();

        return $user->createToken('api')->plainTextToken;
    }
}
