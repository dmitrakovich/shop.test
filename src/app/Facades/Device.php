<?php

namespace App\Facades;

use App\Models\User\Device as UserDevice;

class Device
{
    private static UserDevice $currentDevice;

    private function __construct() {}

    private function __clone() {}

    /**
     * Sets the current device
     *
     * @throws \Exception If a device is already set
     */
    public static function setDevice(UserDevice $device): void
    {
        if (isset(self::$currentDevice)) {
            throw new \Exception('Device already set');
        }

        self::$currentDevice = $device;
    }

    /**
     * Gets the current device
     */
    public static function current(): UserDevice
    {
        return self::$currentDevice;
    }

    /**
     * Gets the ID of the current device
     */
    public static function id(): int
    {
        return self::$currentDevice->id;
    }
}
