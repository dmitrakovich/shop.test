<?php

namespace App\Http\Middleware;

use App\Enums\Cookie as CookieEnum;
use App\Facades\Device as DeviceFacade;
use App\Models\User\Device as UserDevice;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Jenssegers\Agent\Facades\Agent;

class DeviceDetect
{
    /**
     * Web ID for robot devices
     */
    private const WEB_ID_FOR_ROBOT_DEVICES = '6b8945bed1e18bc4f2d1691ac63f5f56';

    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $webId = $this->getDeviceWebId($request);
        if (!$webId) {
            $webId = UserDevice::generateNewWebId($request);
            Cookie::queue(
                cookie(CookieEnum::DEVICE_ID->value, $webId, UserDevice::COOKIE_LIFE_TIME, '/')
            );
            Cache::put($this->getNewDeviceCacheKey($request), $webId, now()->addHours(6));
        }

        DeviceFacade::setDevice(
            UserDevice::query()->firstOrCreate(['web_id' => $webId])
        );

        return $next($request);
    }

    /**
     * Get the web ID for the current device
     */
    private function getDeviceWebId(Request $request): ?string
    {
        if ($this->isRobot($request)) {
            return self::WEB_ID_FOR_ROBOT_DEVICES;
        }

        return $request->cookie(CookieEnum::DEVICE_ID->value);
    }

    /**
     * Check if the current request is from a robot
     */
    private function isRobot(Request $request): bool
    {
        return Agent::isRobot() || $this->isDeviceRecognizedByIpCache($request);
    }

    /**
     * Get the cache key for the new device
     */
    private function getNewDeviceCacheKey(Request $request): string
    {
        return "newDeviceIp:{$request->ip()}";
    }

    /**
     * Check if the device is recognized by IP cache
     */
    private function isDeviceRecognizedByIpCache(Request $request): bool
    {
        return !$request->cookie(CookieEnum::DEVICE_ID->value)
            && Cache::has($this->getNewDeviceCacheKey($request));
    }
}
