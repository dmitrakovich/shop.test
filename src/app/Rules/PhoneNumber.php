<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use libphonenumber\PhoneNumberUtil;

class PhoneNumber implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->fastValidate($value) || !$this->phoneValidate($value)) {
            $fail('Номер телефона имеет неверный формат.');
        }
    }

    /**
     * Performs a quick validation check on a phone number value
     */
    private function fastValidate(mixed $value): bool
    {
        return $value && strlen($value) > 8;
    }

    /**
     * Validates a phone number using the libphonenumber library
     */
    private function phoneValidate(mixed $value): bool
    {
        $phoneUtil = PhoneNumberUtil::getInstance();
        $parsedPhone = $phoneUtil->parse($value, 'BY');

        return $phoneUtil->isValidNumber($parsedPhone);
    }
}
