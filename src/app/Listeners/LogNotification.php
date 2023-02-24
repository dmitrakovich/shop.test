<?php

namespace App\Listeners;

use App\Models\Orders\Order;
use App\Models\User\User;
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
     * @return void
     */
    public function handle(NotificationSent $event)
    {
        $notification = $event->notification;
        if (!($notification instanceof AbstractSmsTraffic)) {
            return;
        }
        /** @var SmsTrafficResponse $response */
        $response = $event->response;
        $notifiable = $event->notifiable;

        $this->logService->logSms(
            phone: $notifiable->routeNotificationFor('smstraffic', $notification),
            text: $notification->getContent(),
            route: $notification->getRoute(),
            userId: $notifiable instanceof User ? $notifiable->id : null,
            orderId: $notifiable instanceof Order ? $notifiable->id : null,
            mailingId: $notification->getMailingId(),
            status: $response->getDescription()
        );
    }
}
