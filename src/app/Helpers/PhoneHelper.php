<?php

namespace App\Helpers;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

class PhoneHelper
{
    /**
     * @throws NumberParseException
     */
    public static function unify(string $phone): int
    {
        $phoneUtil = PhoneNumberUtil::getInstance();
        $formatted = $phoneUtil->format(
            $phoneUtil->parse($phone, 'BY'),
            PhoneNumberFormat::E164
        );

        return (int)ltrim($formatted, '+');
    }
}
