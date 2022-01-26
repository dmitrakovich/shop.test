<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    const DEVICE_ID_COOKIE_NAME = 'device_id';

    /**
     * @var string
     */
    const YANDEX_ID_COOKIE_NAME = '_ym_uid';

    /**
     * @var string
     */
    const GOOGLE_ID_COOKIE_NAME = '_ga';
}
