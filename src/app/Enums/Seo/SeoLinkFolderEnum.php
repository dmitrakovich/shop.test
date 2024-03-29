<?php

namespace App\Enums\Seo;

enum SeoLinkFolderEnum: int
{
    case COUNTRY = 1;

    /**
     * Получить название
     */
    public function name(): ?string
    {
        return match ($this) {
            self::COUNTRY => 'Города',
        };
    }

    /**
     * Получить список
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
