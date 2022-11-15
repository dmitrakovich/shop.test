<?php

namespace App\Listeners;

use App\Models\Orders\Order;
use App\Notifications\AbstractSmsTraffic;
use App\Services\LogService;
use Illuminate\Notifications\Client\Response\SmsTrafficResponse;
use Illuminate\Notifications\Events\NotificationSent;

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
        $notification = $event->notification;
        if (! ($notification instanceof AbstractSmsTraffic)) {
            return;
        }
        /** @var SmsTrafficResponse $response */
        $response = $event->response;
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
