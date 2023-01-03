<?php

namespace App\Providers;

use App\Events\OrderCreated;
use App\Events\ReviewPosted;
use App\Listeners\Cache\ResetUserCache;
use App\Listeners\LogNotification;
use App\Listeners\MergeCart;
use App\Listeners\MergeFavorites;
use App\Listeners\SaveDevice;
use App\Listeners\SendOrderInformationNotification;
use App\Listeners\SyncOrderHistory;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Event;

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
            SyncOrderHistory::class,
            // MergeCart::class,
        ],
        OrderCreated::class => [
            SendOrderInformationNotification::class,
            SaveDevice::class,
            ResetUserCache::class,
        ],
        NotificationSent::class => [
            LogNotification::class,
        ],
        ReviewPosted::class => [
            ResetUserCache::class,
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
