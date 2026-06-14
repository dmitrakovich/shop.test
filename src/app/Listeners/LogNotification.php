<?php

namespace App\Listeners;

use App\Events\Notifications\NotificationSkipped;
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
    public function __construct(private readonly LogService $logService) {}

    /**
     * Handle the event.
     */
    public function handle(NotificationSent|NotificationSkipped $event): void
    {
        $notification = $event->notification;
        if (!$notification instanceof AbstractSmsTraffic) {
            return;
        }

        $notifiable = $event->notifiable;
        [$status, $smsId] = $this->resolveDeliveryMeta($event);

        $this->logService->logSms(
            phone: $notifiable->routeNotificationFor('smstraffic', $notification),
            text: $notification->getContent(),
            route: $notification->getRoute(),
            userId: $notifiable instanceof User ? $notifiable->id : null,
            orderId: $notifiable instanceof Order ? $notifiable->id : null,
            mailingId: $notification->getMailingId(),
            status: $status,
            smsId: $smsId,
        );
    }

    /**
     * @return array{0: ?string, 1: ?string}
     */
    private function resolveDeliveryMeta(NotificationSent|NotificationSkipped $event): array
    {
        if ($event instanceof NotificationSkipped) {
            return [LogService::SMS_SKIPPED_KEY, null];
        }

        $response = $event->response;
        if (!$response instanceof SmsTrafficResponse) {
            return [null, null];
        }

        return [$response->getDescription(), $response->getSmsId()];
    }
}
