<?php

namespace App\Helpers;

use NumberFormatter;

class TextHelper
{

    /**
     * The number in the sum in words.
     * @param float $value value
     * @return string
     */
    public static function numberToMoneyString(float $value): string
    {
        $value = explode('.', number_format($value, 2, '.', ''));
        $numberFormatter = new NumberFormatter('ru', NumberFormatter::SPELLOUT);
        $str = $numberFormatter->format($value[0]);
        $str = mb_strtoupper(mb_substr($str, 0, 1)) . mb_substr($str, 1, mb_strlen($str));
        return $str . ' ' . self::numWord($value[0], ['рубль', 'рубля', 'рублей'], false) . ' ' . self::numWord($value[1], ['копейка', 'копейки', 'копеек']) . '.';
    }

    /**
     * The number in the sum in words (short).
     * @param float $value value
     * @return string
     */
    public static function numberToMoneyShortString(float $value): string
    {
        $value = explode('.', number_format($value, 2, '.', ''));
        return $value[0] . 'руб. ' . $value[1];
    }

    /**
     * Declension of nouns after numerals.
     * @param string $value value
     * @param array $words An array of options, for example: array('товар', 'товара', 'товаров')
     * @param bool $show Includes $value in the resulting string
     * @return string
     */
    public static function numWord(float $value, array $words, bool $show = true): string
    {
        $num = $value % 100;
        if ($num > 19) {
            $num = $num % 10;
        }
        $out = ($show) ?  $value . ' ' : '';
        switch ($num) {
            case 1:
                $out .= $words[0];
                break;
            case 2:
            case 3:
            case 4:
                $out .= $words[1];
                break;
            default:
                $out .= $words[2];
                break;
        }
        return $out;
    }
}
