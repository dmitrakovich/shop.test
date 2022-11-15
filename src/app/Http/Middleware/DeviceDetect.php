<?php

namespace App\Http\Middleware;

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
        if (!$request->hasCookie(Device::DEVICE_ID_COOKIE_NAME)) {
            Cookie::queue(cookie(
                Device::DEVICE_ID_COOKIE_NAME,
                Device::generateId($request),
                Device::COOKIE_LIFE_TIME,
                '/'
            ));
        }

        return $next($request);
    }
}
