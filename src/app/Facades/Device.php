<?php

namespace App\Facades;

use App\Models\User\Device as UserDevice;
use LogicException;

class Device
{
    private static ?UserDevice $currentDevice = null;

    private function __construct() {}

    private function __clone() {}

    /**
     * Sets the current device
     *
     * @throws \Exception If a device is already set
     */
    public static function setDevice(UserDevice $device): void
    {
        if (self::isResolved()) {
            throw new \Exception('Device already set');
        }

        self::$currentDevice = $device;
    }

    /**
     * Sets the current device to the console device
     */
    public static function setConsoleDevice(): void
    {
        self::$currentDevice = UserDevice::console();
    }

    /**
     * Gets the current device
     *
     * @throws LogicException If no device has been set
     */
    public static function current(): UserDevice
    {
        if (!self::isResolved()) {
            throw new LogicException('Device has not been set.');
        }

        return self::$currentDevice;
    }

    /**
     * Whether a device has been set for the current request or process.
     */
    public static function isResolved(): bool
    {
        return self::$currentDevice !== null;
    }

    /**
     * Gets the ID of the current device
     *
     * @throws LogicException If no device has been set
     */
    public static function id(): int
    {
        return self::current()->id;
    }
}
