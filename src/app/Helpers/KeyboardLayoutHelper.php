<?php

namespace App\Helpers;

class KeyboardLayoutHelper
{
    /**
     * Physical keys, English layout then Russian ЙЦУКЕН, including punctuation.
     */
    private const string LATIN = 'qwertyuiop[]asdfghjkl;\'zxcvbnm,./`QWERTYUIOP{}ASDFGHJKL:"ZXCVBNM<>?~';

    private const string CYRILLIC = 'йцукенгшщзхъфывапролджэячсмитьбю.ёЙЦУКЕНГШЩЗХЪФЫВАПРОЛДЖЭЯЧСМИТЬБЮ,Ё';

    /**
     * Switch a query typed in the wrong layout.
     *
     * Returns null when the text has both alphabets or none (digits and punctuation only).
     */
    public static function switch(string $text): ?string
    {
        $hasLatin = preg_match('/[A-Za-z]/', $text) === 1;
        $hasCyrillic = preg_match('/\p{Cyrillic}/u', $text) === 1;

        if ($hasLatin === $hasCyrillic) {
            return null;
        }

        $latin = mb_str_split(self::LATIN);
        $cyrillic = mb_str_split(self::CYRILLIC);

        return strtr($text, $hasLatin ? array_combine($latin, $cyrillic) : array_combine($cyrillic, $latin));
    }
}
