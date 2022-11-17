<?php

namespace App\Libraries\Seo\Facades;

use App\Libraries\Seo\Seo;

use Illuminate\Support\Facades\Facade;

class SeoFacade extends Facade
{

    protected static function getFacadeAccessor()
    {
        return Seo::class;
    }
}
