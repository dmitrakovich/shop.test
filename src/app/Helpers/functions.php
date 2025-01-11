<?php

use App\Contracts\ClientInterface;
use App\Facades\Device;
use App\Models\User\User;
use Illuminate\Support\Facades\Auth;

// for $absolute = false
if (!function_exists('route')) {
    function route($name, $parameters = [], $absolute = false)
    {
        return app('url')->route($name, $parameters, $absolute);
    }
}

/**
 * Get the current client (authorized user or device).
 */
function client(): ClientInterface
{
    if (($user = Auth::user()) instanceof User) {
        return $user;
    }

    return Device::current();
}
