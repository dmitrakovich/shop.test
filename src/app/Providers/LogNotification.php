<?php

namespace App\Providers;

use App\Models\Orders\Order;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\AbstractSmsTraffic;
use App\Services\LogService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Notifications\Client\Response\SmsTrafficResponse;

class LogNotification
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(private LogService $logService)
    {
    }

    /**
     * Handle the event.
     *
     * @param  \Illuminate\Notifications\Events\NotificationSent  $event
     * @return void
     */
    public function handle(NotificationSent $event)
    {
        /** @var SmsTrafficResponse $response */
        $response = $event->response;
        /** @var AbstractSmsTraffic $notification */
        $notification = $event->notification;
        $notifiable = $event->notifiable;

        $this->logService->logSms(
            $notifiable->routeNotificationFor('smstraffic', $notification),
            $notification->getContent(),
            $notification->getRoute(),
            null,
            $notifiable instanceof Order ? $notifiable->id : null,
            $response->getDescription()
        );
    }
}
