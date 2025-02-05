<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CaptchaScore implements ValidationRule
{
    public function __construct(private int $acceptable = 6)
    {
        //
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_numeric($value) || $value < $this->acceptable) {
            $fail('Captcha verification failed. Please try again.');
        }
    }
}
