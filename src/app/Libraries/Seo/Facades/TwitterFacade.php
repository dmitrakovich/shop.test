<?php

namespace App\Libraries\Seo\Facades;

use App\Libraries\Seo\Twitter;
use Illuminate\Support\Facades\Facade;

class TwitterFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Twitter::class;
    }
}
