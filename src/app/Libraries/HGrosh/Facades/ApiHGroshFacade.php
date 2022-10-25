<?php

namespace App\Libraries\HGrosh\Facades;

use App\Libraries\HGrosh\Api;
use Illuminate\Support\Facades\Facade;

class ApiHGroshFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Api::class;
    }
}
