<?php

namespace App\Providers;

use App\Events\OrderCreated;
use App\Listeners\MergeCart;
use App\Listeners\SaveDevice;
use App\Listeners\MergeFavorites;
use Illuminate\Auth\Events\Login;
use App\Listeners\SyncOrderHistory;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use App\Listeners\SendOrderInformationNotification;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
            // SyncOrderHistory::class,
        ],
        Login::class => [
            SaveDevice::class,
            MergeFavorites::class,
            // MergeCart::class,
        ],
        OrderCreated::class => [
            SendOrderInformationNotification::class,
            SaveDevice::class,
        ],
        NotificationSent::class => [
            LogNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
