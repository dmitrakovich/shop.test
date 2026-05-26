<?php

namespace App\Rules;

use App\Models\User\User;
use App\ValueObjects\Phone;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use libphonenumber\NumberParseException;

class UniqueUserPhone implements ValidationRule
{
    public function __construct(private readonly ?int $ignoreUserId = null) {}

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        try {
            $phone = Phone::fromRawString((string)$value)->forSave();
        } catch (NumberParseException) {
            return;
        }

        $query = User::query()->where('phone', $phone);

        if ($this->ignoreUserId !== null) {
            $query->whereKeyNot($this->ignoreUserId);
        }

        $exists = $query->exists();

        if ($exists) {
            $fail('Пользователь с таким телефоном уже существует.');
        }
    }
}
