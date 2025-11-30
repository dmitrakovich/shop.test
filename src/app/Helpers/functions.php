<?php

use App\Contracts\ClientInterface;
use App\Facades\Device;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
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
 *
 * @return ClientInterface<covariant Model>
 */
function client(): ClientInterface
{
    if (($user = Auth::user()) instanceof User) {
        return $user;
    }

    return Device::current();
}

/**
 * Get the currently authenticated user.
 */
function user(): User
{
    return Auth::user();
}

/**
 * Get the currently authenticated user.
 */
function authUser(): ?User
{
    if (($user = Auth::user()) instanceof User) {
        return $user;
    }

    return null;
}
