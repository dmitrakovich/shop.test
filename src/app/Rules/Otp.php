<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Otp implements ValidationRule
{
    public const ERROR_MSG = 'Неверный код';

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->isValid($value)) {
            $fail(self::ERROR_MSG);
        }
    }

    /**
     * Validates if the given OTP value is valid
     */
    private function isValid(mixed $otp): bool
    {
        return is_string($otp) && strlen($otp) === 6 && is_numeric($otp);
    }
}
