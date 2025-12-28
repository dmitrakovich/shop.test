<?php

use App\Contracts\ClientInterface;
use App\Facades\Device;
use App\Models\User\User;
use Filament\Support\Contracts\HasLabel;
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
 * Convert backed enum implementing HasLabel to legacy associative array.
 *
 * @param class-string<covariant BackedEnum> $enumClass
 *
 * @return array<int|string, string>
 */
function enum_to_array(string $enumClass): array
{
    if (!enum_exists($enumClass)) {
        throw new InvalidArgumentException("Class '$enumClass' is not a valid enum.");
    }

    if (!is_subclass_of($enumClass, HasLabel::class)) {
        throw new InvalidArgumentException("Enum '$enumClass' must implement HasLabel interface.");
    }

    $result = [];
    /** @var BackedEnum&HasLabel $case */
    foreach ($enumClass::cases() as $case) {
        $result[$case->value] = $case->getLabel();
    }

    return $result;
}
