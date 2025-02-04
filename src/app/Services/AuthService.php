<?php

namespace App\Services;

use App\Events\Analytics\Registered;
use App\Models\User\User;
use App\Notifications\VerificationPhoneSms;
use Illuminate\Support\Facades\Session;

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
    public function validateOTP(?int $enteredOtp): bool
    {
        $otp = Session::get('otp');

        return $otp === $enteredOtp;
    }
}
