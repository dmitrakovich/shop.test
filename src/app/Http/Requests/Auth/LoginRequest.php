<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Services\AuthService;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class LoginRequest extends FormRequest
{
    /**
     * max send sms attempts per minute
     */
    const SMS_THROTTLE = 1;

    /**
     * max enter otp attempts per minute
     */
    const OTP_THROTTLE = 3;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'phone' => ['required', 'string'],
            'otp' => ['nullable'],
            'captcha_score' => ['required', 'numeric', 'gt:6'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): User
    {
        $this->ensureIsNotRateLimited();
        /** @var AuthService $authService */
        $authService = app(AuthService::class);
        $user = $authService->getOrCreateUser($this->input('phone'));

        if (!$this->has('otp')) {
            $this->ensureIsSmsNotRateLimited();
            $authService->generateNewOTP($user);
            RateLimiter::hit($this->throttleKeyForSms());

            $this->returnBack();
        }

        if (!$authService->validateOTP($this->input('otp'))) {
            RateLimiter::hit($this->throttleKeyForOTP());

            $this->returnBack(['otp' => __('auth.otp_failed')]);
        }

        $user->updatePhoneVerifiedAt();
        Auth::login($user, true);

        RateLimiter::clear($this->throttleKeyForSms());
        RateLimiter::clear($this->throttleKeyForOTP());

        return $user;
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @return void|never
     */
    public function ensureIsNotRateLimited()
    {
        if (RateLimiter::tooManyAttempts($this->throttleKeyForOTP(), self::OTP_THROTTLE)) {
            event(new Lockout($this));
            $this->returnBack(['otp' => __('auth.throttle', [
                'seconds' => RateLimiter::availableIn($this->throttleKeyForOTP()),
            ])]);
        }
    }

    /**
     * Ensure the sms is not rate limited.
     *
     * @return void|never
     */
    public function ensureIsSmsNotRateLimited()
    {
        if (RateLimiter::tooManyAttempts($this->throttleKeyForSms(), self::SMS_THROTTLE)) {
            event(new Lockout($this));
            $this->returnBack(['phone' => 'Попробуйте позже']);
        }
    }

    /**
     * Return to login page
     */
    public function returnBack(array $errors = []): never
    {
        $response = back()->with([
            'smsThrottle' => RateLimiter::availableIn($this->throttleKeyForSms()),
        ]);
        $response->withInput($this->only('phone'));
        if (!empty($errors)) {
            $response->withErrors($errors);
        }

        abort($response);
    }

    /**
     * Get the rate limiting throttle key for sms to phone.
     */
    public function throttleKeyForSms(): string
    {
        return 'phone:' . Str::lower($this->input('phone')) . '|' . $this->ip();
    }

    /**
     * Get the rate limiting throttle key for otp (one time password)
     */
    public function throttleKeyForOTP(): string
    {
        return 'otp:' . Str::lower($this->input('phone')) . '|' . $this->ip();
    }
}
