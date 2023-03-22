<?php

namespace App\Listeners;

use App\Events\Notifications\NotificationSkipped;
use App\Models\Orders\Order;
use App\Models\User\User;
use App\Notifications\AbstractSmsTraffic;
use App\Services\LogService;
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
    public function handle(NotificationSent|NotificationSkipped $event)
    {
        $notification = $event->notification;
        if (!($notification instanceof AbstractSmsTraffic)) {
            return;
        }

        $status = match (get_class($event)) {
            NotificationSent::class => $event->response->getDescription(),
            NotificationSkipped::class => $this->logService::SMS_SKIPPED_KEY,
        };
        $notifiable = $event->notifiable;

        $this->logService->logSms(
            phone: $notifiable->routeNotificationFor('smstraffic', $notification),
            text: $notification->getContent(),
            route: $notification->getRoute(),
            userId: $notifiable instanceof User ? $notifiable->id : null,
            orderId: $notifiable instanceof Order ? $notifiable->id : null,
            mailingId: $notification->getMailingId(),
            status: $status
        );
    }
}
