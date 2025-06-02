<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\Traits\HasRateLimiting;
use App\Rules\CaptchaScore;
use App\Rules\Otp;
use App\Rules\PhoneNumber;
use App\Services\UserService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class LoginAttemptRequest extends FormRequest
{
    use HasRateLimiting;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'phone' => new PhoneNumber(),
            'otp' => new Otp(),
            'captcha_score' => new CaptchaScore(),
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->applyRateLimit(3);
    }

    /**
     * Handle a passed validation attempt.
     */
    protected function passedValidation(): void
    {
        $user = app(UserService::class)->findOrFailByPhone($this->input('phone'));

        if (!$user->validateOtp($this->input('otp'))) {
            throw ValidationException::withMessages(['otp' => Otp::ERROR_MSG]);
        }

        $this->setUserResolver(fn () => $user);
    }
}
