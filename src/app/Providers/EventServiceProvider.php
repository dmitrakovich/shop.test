<?php

namespace App\Providers;

use App\Events\Analytics;
use App\Events\Notifications\NotificationSkipped;
use App\Events\OrderCreated;
use App\Events\OrderStatusChanged;
use App\Events\Products;
use App\Events\ReviewPosted;
use App\Events\User\UserLogin;
use App\Listeners\Cache\ResetUserCache;
use App\Listeners\FacebookPixel;
use App\Listeners\GoogleTag;
use App\Listeners\LogNotification;
use App\Listeners\Media\ConvertVideo;
use App\Listeners\MergeCart;
use App\Listeners\MergeFavorites;
use App\Listeners\OneC;
use App\Listeners\Order\CreateInstallment;
use App\Listeners\Order\DistributeOrder;
use App\Listeners\Product;
use App\Listeners\Promo\ApplyPendingPromocode;
use App\Listeners\SaveDevice;
use App\Listeners\SendOrderInformationNotification;
use App\Listeners\SyncOrderHistory;
use App\Listeners\UpdateInventory;
use App\Listeners\UpdateOrderItemsStatus;
use App\Listeners\User\HandleLogin;
use App\Listeners\User\UpdateUserAfterOrder;
use App\Observers;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Notifications\Events\NotificationSent;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent;

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
            HandleLogin::class,
        ],
        UserLogin::class => [
            SaveDevice::class,
            MergeFavorites::class,
            SyncOrderHistory::class,
            ApplyPendingPromocode::class,
            MergeCart::class,
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
            UpdateUserAfterOrder::class,
            ResetUserCache::class,
            CreateInstallment::class,
            DistributeOrder::class,
            UpdateInventory::class,
        ],
        Analytics\Purchase::class => [
            GoogleTag\SetPurchaseData::class,
            FacebookPixel\SendPurchaseEvent::class,
        ],
        Analytics\OfflinePurchase::class => [
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
        MediaHasBeenAddedEvent::class => [
            ConvertVideo::class,
        ],
        Products\ProductCreated::class => [
            Product\GenerateSlug::class,
            OneC\UpdateProduct::class,
        ],
        Products\ProductUpdated::class => [
            OneC\UpdateProduct::class,
        ],
    ];

    /**
     * The model observers for your application.
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
        \App\Models\Payments\OnlinePayment::class => [
            Observers\OnlinePaymentObserver::class,
        ],
    ];
}
