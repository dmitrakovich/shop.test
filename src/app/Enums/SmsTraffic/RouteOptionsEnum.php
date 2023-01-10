<?php

namespace App\Enums\SmsTraffic;

enum RouteOptionsEnum: string
{
    case SMS = 'sms';
    case VIBER = 'viber';
    case SMS_VIBER = 'viber(60)-sms';

    /**
     * Get name
     *
     * @return string
     */
    public function name(): ?string
    {
        return match ($this) {
            self::SMS => 'SMS',
            self::VIBER => 'Viber',
            self::SMS_VIBER => 'Vb/SMS',
        };
    }

    /**
     * Get list
     *
     * @return array
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
