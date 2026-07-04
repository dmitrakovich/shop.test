<?php

namespace App\Http\Middleware;

use App\Facades\Device as DeviceFacade;
use App\Models\User\Device as UserDevice;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class DeviceAuth
{
    private const string DEVICE_ID_HEADER_KEY = 'device-id';

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $deviceId = $request->header(self::DEVICE_ID_HEADER_KEY);

        abort_unless(Str::isUuid($deviceId), Response::HTTP_UNAUTHORIZED, 'Invalid device ID');

        $device = UserDevice::query()->firstOrCreate(['api_id' => $deviceId]);

        abort_if($device->isBanned(), Response::HTTP_FORBIDDEN, 'Device blocked');

        DeviceFacade::setDevice($device);

        return $next($request);
    }
}
