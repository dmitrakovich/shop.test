<?php

namespace App\Http\Middleware;

use App\Facades\Device as DeviceFacade;
use App\Models\User\Device as UserDevice;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DeviceAuth
{
    private const DEVICE_ID_HEADER_KEY = 'device-id';

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // ! hotfix start
        // todo: remove after migrate to new site
        if ($oldDeviceId = $request->header('old-device-id')) {
            if ($device = UserDevice::query()->firstWhere('web_id', $oldDeviceId)) {
                DeviceFacade::setDevice($device);

                return $next($request);
            }
        }
        // ! hotfix end

        $deviceId = $request->header(self::DEVICE_ID_HEADER_KEY);

        abort_unless($deviceId, Response::HTTP_UNAUTHORIZED, 'Device ID is missing');

        DeviceFacade::setDevice(
            UserDevice::query()->firstOrCreate(['api_id' => $deviceId])
        );

        return $next($request);
    }
}
