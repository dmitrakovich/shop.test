<?php

namespace App\Providers;

use App\Events\Notifications\NotificationSkipped;
use App\Events\OrderCreated;
use App\Events\ReviewPosted;
use App\Listeners\Cache\ResetUserCache;
use App\Listeners\LogNotification;
use App\Listeners\MergeCart;
use App\Listeners\MergeFavorites;
use App\Listeners\SaveDevice;
use App\Listeners\SendOrderInformationNotification;
use App\Listeners\SyncOrderHistory;
use App\Listeners\User\UpdateUserGroup;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
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
            UpdateUserGroup::class,
            ResetUserCache::class,
        ],
        NotificationSent::class => [
            LogNotification::class,
        ],
        NotificationSkipped::class => [
            LogNotification::class,
        ],
        ReviewPosted::class => [
            ResetUserCache::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
