<?php

namespace App\Models;

use Illuminate\Http\Request;
use Jenssegers\Agent\Facades\Agent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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

    /**
     * @var integer 1 year
     */
    const COOKIE_LIFE_TIME = 525600;

    /**
     * Generate device id for new device
     *
     * @param Request $request
     * @return string
     */
    public static function generateId(Request $request): string
    {
        return md5(
            uniqid($request->getHost()) . $request->ip()
        );
    }





    public function agent()
    {
        $browser = Agent::browser();
        $version = Agent::version($browser);

        $platform = Agent::platform();
        $Pversion = Agent::version($platform);

        dd(
            // $request->cookie(),
            // $request->coo
            Agent::device(),
            Agent::isDesktop(),
            Agent::isPhone(),
            "$platform $Pversion; $browser $version",
            // $request->server()['HTTP_USER_AGENT']
        );
    }
}
