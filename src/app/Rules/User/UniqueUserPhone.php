<?php

namespace App\Rules\User;

use App\Models\User\User;
use App\ValueObjects\Phone;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use libphonenumber\NumberParseException;

class UniqueUserPhone implements ValidationRule
{
    public function __construct(private readonly ?int $ignoreUserId = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        try {
            $phone = Phone::fromRawString((string)$value);
        } catch (NumberParseException) {
            $fail('Некорректный номер телефона.');

            return;
        }

        $query = User::query()->where('phone', $phone->forSave());

        if ($this->ignoreUserId !== null) {
            $query->whereKeyNot($this->ignoreUserId);
        }

        if ($query->exists()) {
            $fail('Пользователь с таким телефоном уже существует.');
        }
    }
}
