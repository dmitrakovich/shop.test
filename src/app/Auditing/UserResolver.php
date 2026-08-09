<?php

namespace App\Auditing;

use App\Facades\Device as DeviceFacade;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use OwenIt\Auditing\Contracts\UserResolver as UserResolverContract;

class UserResolver implements UserResolverContract
{
    /**
     * Resolve the authenticated user, or the current API/web device for guests.
     *
     * @return Authenticatable|Model|null
     */
    public static function resolve()
    {
        foreach (Config::get('audit.user.guards', [Config::get('auth.defaults.guard')]) as $guard) {
            try {
                if (Auth::guard($guard)->check()) {
                    return Auth::guard($guard)->user();
                }
            } catch (\Throwable) {
                continue;
            }
        }

        if (!DeviceFacade::isResolved()) {
            return null;
        }

        $device = DeviceFacade::current();

        // Skip ephemeral console devices created in AppServiceProvider.
        if (!$device->exists) {
            return null;
        }

        return $device;
    }
}
