<?php

namespace App\Helpers;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
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

    public static function digitsOnly(string $value): string
    {
        return preg_replace('/\D+/', '', $value) ?? '';
    }

    /**
     * Match phone by digits only (works for digit columns and masked strings).
     *
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public static function constrainSearch(Builder $query, string $search, string $column = 'phone'): Builder
    {
        $digits = self::digitsOnly($search);

        if ($digits === '') {
            return $query->whereRaw('0 = 1');
        }

        $wrappedColumn = $query->getGrammar()->wrap($column);

        return $query->whereRaw(
            "REGEXP_REPLACE({$wrappedColumn}, '[^0-9]', '') LIKE ?",
            ["%{$digits}%"],
        );
    }

    /**
     * Filament TextColumn::searchable(query: …) callback.
     *
     * @return Closure(Builder<Model>, string): Builder<Model>
     */
    public static function searchableQuery(string $column = 'phone'): Closure
    {
        return static function (Builder $query, string $search) use ($column): Builder {
            return self::constrainSearch($query, $search, $column);
        };
    }
}
