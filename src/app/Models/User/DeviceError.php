<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $device_id
 * @property string $error_code
 * @property string $error_message
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class DeviceError extends Model
{
    public const int BEFORE_BAN_COUNT = 20;

    /**
     * Indicates if all mass assignment is enabled.
     *
     * @var bool
     */
    protected static $unguarded = true;
}
