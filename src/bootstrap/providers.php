<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\EventServiceProvider::class,
    Drandin\DeclensionNouns\DeclensionNounsServiceProvider::class,
    App\Providers\CartServiceProvider::class,
    App\Providers\GoogleTagManagerProvider::class,
    App\Libraries\Seo\Providers\SeoServiceProvider::class,
];
