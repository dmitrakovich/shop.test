<?php

use App\Libraries\Seo\Providers\SeoServiceProvider;
use App\Providers\AdminPanelProvider;
use App\Providers\AppServiceProvider;
use App\Providers\BelpostServiceProvider;
use App\Providers\EventServiceProvider;
use App\Providers\GoogleTagManagerProvider;
use App\Providers\HorizonServiceProvider;
use App\Providers\RouteServiceProvider;
use Drandin\DeclensionNouns\DeclensionNounsServiceProvider;

return [
    SeoServiceProvider::class,
    AdminPanelProvider::class,
    AppServiceProvider::class,
    BelpostServiceProvider::class,
    EventServiceProvider::class,
    GoogleTagManagerProvider::class,
    HorizonServiceProvider::class,
    RouteServiceProvider::class,
    DeclensionNounsServiceProvider::class,
];
