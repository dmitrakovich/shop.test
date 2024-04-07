<?php

namespace App\Http\Middleware;

use App\Enums\Cookie as CookieEnum;
use App\Models\Device;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class DeviceDetect
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$request->hasCookie(CookieEnum::DEVICE_ID->value)) {
            Cookie::queue(cookie(
                CookieEnum::DEVICE_ID->value,
                Device::generateNewId($request),
                Device::COOKIE_LIFE_TIME,
                '/'
            ));
        }

        return $next($request);
    }
}
