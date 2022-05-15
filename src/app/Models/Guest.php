<?php

namespace App\Models;

use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;

/**
 * Class Guest
 */
class Guest
{
    /**
     * @var string
     */
    final const COOKIE_NAME = 'guest_data';

    /**
     * @var integer 1 year
     */
    final const COOKIE_LIFE_TIME = 525600;

    /**
     * Save guest data
     */
    public static function setData(array $data): void
    {
        if (self::checkData($data)) {
            Cookie::queue(
                self::COOKIE_NAME,
                json_encode($data, JSON_UNESCAPED_UNICODE),
                self::COOKIE_LIFE_TIME,
            );
        }
    }

    /**
     * Return guest data
     */
    public static function getData(): array
    {
        return json_decode(Cookie::get(self::COOKIE_NAME, '[]'), true);
    }

    /**
     * Check data before save
     */
    protected static function checkData(array $data): bool
    {
        if (!empty($data['phone']) || !empty($data['email'])) {
            return true;
        } else {
            Log::warning('Wrong guest data! data: ' . json_encode($data));
            return false;
        }
    }
}
