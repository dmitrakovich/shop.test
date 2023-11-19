<?php

namespace App\Providers;

use App\Events\Analytics;
use App\Events\Notifications\NotificationSkipped;
use App\Events\OrderCreated;
use App\Events\OrderStatusChanged;
use App\Events\ReviewPosted;
use App\Listeners\Cache\ResetUserCache;
use App\Listeners\FacebookPixel;
use App\Listeners\GoogleTag;
use App\Listeners\LogNotification;
use App\Listeners\MergeCart;
use App\Listeners\MergeFavorites;
use App\Listeners\SaveDevice;
use App\Listeners\SendOrderInformationNotification;
use App\Listeners\SyncOrderHistory;
use App\Listeners\UpdateInventory;
use App\Listeners\UpdateOrderItemsStatus;
use App\Listeners\User\UpdateUserGroup;
use App\Observers;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Notifications\Events\NotificationSent;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Analytics\Registered::class => [
            GoogleTag\SetUserRegistrationData::class,
            FacebookPixel\SendCompleteRegistrationEvent::class,
            // SyncOrderHistory::class,
        ],
        Login::class => [
            SaveDevice::class,
            MergeFavorites::class,
            SyncOrderHistory::class,
            // MergeCart::class,
        ],
        Analytics\ProductView::class => [
            GoogleTag\SetProductViewData::class,
            FacebookPixel\SendProductViewEvent::class,
        ],
        Analytics\AddToCart::class => [
            FacebookPixel\SendAddToCartEvent::class,
        ],
        OrderCreated::class => [
            SendOrderInformationNotification::class,
            SaveDevice::class,
            UpdateUserGroup::class,
            ResetUserCache::class,
            UpdateInventory::class,
        ],
        Analytics\Purchase::class => [
            GoogleTag\SetPurchaseData::class,
            FacebookPixel\SendPurchaseEvent::class,
        ],
        OrderStatusChanged::class => [
            UpdateOrderItemsStatus::class,
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
        Analytics\SocialSubscription::class => [
            FacebookPixel\SendLeadEvent::class,
        ],
    ];

    /**
     * The model observers for your application.
     *
     * @var array
     */
    protected $observers = [
        \App\Models\Orders\Order::class => [
            Observers\OrderObserver::class,
        ],
        \App\Models\Orders\OrderItem::class => [
            Observers\OrderItemObserver::class,
        ],
        \App\Models\Orders\OrderItemExtended::class => [
            Observers\OrderItemObserver::class,
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
