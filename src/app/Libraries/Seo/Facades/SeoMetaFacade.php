<?php

namespace App\Libraries\Seo\Facades;

use App\Libraries\Seo\SeoMeta;

use Illuminate\Support\Facades\Facade;

class SeoMetaFacade extends Facade
{

    protected static function getFacadeAccessor()
    {
        return SeoMeta::class;
    }
}
