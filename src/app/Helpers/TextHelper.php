<?php

namespace App\Helpers;

use Drandin\DeclensionNouns\Facades\DeclensionNoun;
use NumberFormatter;

class TextHelper
{
    /**
     * The number in the sum in words.
     *
     * @param  float  $value value
     */
    public static function numberToMoneyString(float $value): string
    {
        $value = explode('.', number_format($value, 2, '.', ''));
        $numberFormatter = new NumberFormatter('ru', NumberFormatter::SPELLOUT);
        $str = $numberFormatter->format($value[0]);
        $str = mb_strtoupper(mb_substr($str, 0, 1)) . mb_substr($str, 1, mb_strlen($str));

        return $str . ' ' . DeclensionNoun::makeOnlyWord($value[0], 'рубль') . ' ' . DeclensionNoun::make($value[1], 'копейка') . '.';
    }

    /**
     * The number in the sum in words (short).
     *
     * @param  float  $value value
     */
    public static function numberToMoneyShortString(float $value): string
    {
        $value = explode('.', number_format($value, 2, '.', ''));

        return $value[0] . 'руб. ' . $value[1];
    }
}
