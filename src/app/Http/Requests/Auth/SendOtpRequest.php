<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\Traits\HasRateLimiting;
use App\Rules\CaptchaScore;
use App\Rules\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;

class SendOtpRequest extends FormRequest
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
            'captcha_score' => new CaptchaScore(),
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        $this->applyRateLimit(1);
    }
}
