<?php

namespace App\Libraries\Seo\Providers;

use App\Libraries\Seo\OpenGraph;
use App\Libraries\Seo\Seo;
use App\Libraries\Seo\SeoMeta;
use App\Libraries\Seo\Twitter;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class SeoServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * @return void
     */
    public function boot() {}

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('seo.meta', function ($app) {
            return new SeoMeta(config('seo.meta', []));
        });
        $this->app->singleton('seo.opengraph', function ($app) {
            return new OpenGraph(config('seo.opengraph', []));
        });
        $this->app->singleton('seo.twitter', function ($app) {
            return new Twitter(config('seo.twitter', []));
        });
        $this->app->singleton('seo', function ($app) {
            return new Seo();
        });
    }

    public function provides()
    {
        return [
            'seo',
            'seo.meta',
            'seo.opengraph',
            'seo.twitter',
        ];
    }
}
