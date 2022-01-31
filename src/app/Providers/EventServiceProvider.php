<?php

namespace App\Providers;

use App\Events\OrderCreated;
use App\Listeners\MergeFavorites;
use App\Listeners\SaveDevice;
use App\Listeners\SendOrderInformationNotification;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        Login::class => [
            SaveDevice::class,
            MergeFavorites::class,
        ],
        OrderCreated::class => [
            SendOrderInformationNotification::class,
            SaveDevice::class,
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
}
