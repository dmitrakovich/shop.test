<?php

namespace App\Providers;

use App\Contracts\OrderServiceInterface;
use App\Database\SqlServerConnection;
use App\Logging\FacebookApiLogger;
use App\Notifications\ChannelManagerWithLimits;
use App\Policies\RolePolicy;
use App\Services\Api\Facebook\ConversionsApiService;
use App\Services\CartService;
use App\Services\OrderService;
use FacebookAds\Api;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Sentry\Severity;
use Spatie\Permission\Models\Role;

use function Sentry\captureMessage;

class AppServiceProvider extends ServiceProvider
{
    public $bindings = [
        OrderServiceInterface::class => OrderService::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ChannelManager::class, function ($app) {
            return new ChannelManagerWithLimits($app);
        });

        $this->app->singleton('cart', fn () => app(CartService::class)->initCart());

        $this->app->singleton(ConversionsApiService::class, function () {
            $pixelId = config('services.facebook.pixel_id');
            $api = Api::init(null, null, config('services.facebook.token'));
            // $logger = new FacebookApiLogger(fopen(config('services.facebook.log_file'), 'a'));
            // $api->setLogger($logger->setJsonPrettyPrint(true));

            return new ConversionsApiService($api, $pixelId);
        });

        Connection::resolverFor('sqlsrv', function ($connection, $database, $prefix, $config) {
            return (new SqlServerConnection($connection, $database, $prefix, $config))->createTunnel();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /** @var \Illuminate\Foundation\Application */
        $app = $this->app;

        Paginator::useBootstrap();

        setlocale(LC_TIME, 'ru_RU.UTF-8');
        Carbon::setLocale(config('app.locale'));

        // $this->modelShouldBeStrict($app->isProduction());

        // $this->logQueries();

        if ($app->isLocal()) {
            $app['config']['filesystems.disks.public.url'] = 'https://barocco.by/media';
        }

        JsonResource::withoutWrapping();

        Gate::policy(Role::class, RolePolicy::class);
    }

    /**
     * Model::shouldBeStrict()
     */
    private function modelShouldBeStrict(bool $isProduction): void
    {
        Model::preventAccessingMissingAttributes();
        // Warn us when we try to set an unfillable property.
        Model::preventSilentlyDiscardingAttributes();

        Model::preventLazyLoading(!$isProduction);
        // if ($isProduction) {
        //     Model::handleLazyLoadingViolationUsing(function ($model, $relation) {
        //         $class = get_class($model);

        //         captureMessage("Attempted to lazy load [{$relation}] on model [{$class}].", Severity::warning());
        //     });
        // }
    }

    private function logQueries(): void
    {
        DB::listen(function ($query) {
            $sql = $query->sql;
            $bindings = $query->bindings;
            $executionTime = $query->time;

            Log::debug($sql, compact('bindings', 'executionTime'));
        });
    }
}
