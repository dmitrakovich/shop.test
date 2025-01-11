<?php

namespace App\Http\Middleware;

use App\Enums\Cookie as CookieEnum;
use App\Facades\Device as DeviceFacade;
use App\Models\User\Device as UserDevice;
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
        $webId = $request->cookie(CookieEnum::DEVICE_ID->value);
        if (!$webId) {
            $webId = UserDevice::generateNewWebId($request);
            Cookie::queue(
                cookie(CookieEnum::DEVICE_ID->value, $webId, UserDevice::COOKIE_LIFE_TIME, '/')
            );
        }

        DeviceFacade::setDevice(
            UserDevice::query()->firstOrCreate(['web_id' => $webId])
        );

        return $next($request);
    }
}
