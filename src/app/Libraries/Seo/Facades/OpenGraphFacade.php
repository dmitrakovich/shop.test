<?php

namespace App\Libraries\Seo\Facades;

use App\Libraries\Seo\OpenGraph;
use Illuminate\Support\Facades\Facade;

class OpenGraphFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return OpenGraph::class;
    }
}
