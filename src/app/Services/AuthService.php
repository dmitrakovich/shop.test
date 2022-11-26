<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\VerificationPhoneSms;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Session;

/**
 * Class AuthService
 */
class AuthService
{
    /**
     * Minimum value of one-time password
     */
    const OTP_MIN_VALUE = 100000;

    /**
     * Maximum value of one-time password
     */
    const OTP_MAX_VALUE = 999999;

    /**
     * Key for session storage
     */
    const OTP_SESSION_KEY = 'otp';

    /**
     * AuthService constructor.
     */
    public function __construct(private User $user)
    {
    }

    /**
     * Find user or create new by phone number
     */
    public function getOrCreateUser(string $phone): User
    {
        /** @var User $user */
        $user = $this->user->getByPhone($phone) ?? $this->user->query()->create([
            'phone' => $phone,
        ]);

        if ($user->wasRecentlyCreated) {
            event(new Registered($user));
        }

        return $user;
    }

    /**
     * Generate, send & save new one-time password
     */
    public function generateNewOTP(User $user): int
    {
        $otp = mt_rand(self::OTP_MIN_VALUE, self::OTP_MAX_VALUE);
        $user->notify(new VerificationPhoneSms($otp));
        Session::put(self::OTP_SESSION_KEY, $otp);

        return $otp;
    }

    /**
     * Validate one-time password
     */
    public function validateOTP(?int $enteredOtp): bool
    {
        $otp = Session::get(self::OTP_SESSION_KEY);

        return $otp === $enteredOtp;
    }
}
