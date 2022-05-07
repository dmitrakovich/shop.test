<?php

namespace App\Http\Middleware;

use App\Models\Device;
use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

class EncryptCookies extends Middleware
{
    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array<int, string>
     */
    protected $except = [
        'utm',
        Device::YANDEX_ID_COOKIE_NAME,
        Device::GOOGLE_ID_COOKIE_NAME,
    ];
}
