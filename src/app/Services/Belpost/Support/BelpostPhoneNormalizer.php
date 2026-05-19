<?php

namespace App\Services\Belpost\Support;

class BelpostPhoneNormalizer
{
    public function normalize(mixed $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $digits = preg_replace('/\D/', '', (string)$phone);

        if ($digits === null || $digits === '') {
            return null;
        }

        return substr($digits, -12);
    }
}
