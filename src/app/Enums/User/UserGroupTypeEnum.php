<?php

namespace App\Enums\User;

enum UserGroupTypeEnum: int
{
    case REGISTERED = 1;

    /**
     * Get name
     *
     * @return string
     */
    public function name(): ?string
    {
        return match ($this) {
            self::REGISTERED => 'Зарегистрированные',
        };
    }


    /**
     * Get list
     */
    public static function list(): array
    {
        $result = [];
        $cases = self::cases();
        foreach ($cases as $case) {
            $result[$case->value] = $case->name();
        }

        return $result;
    }
}
