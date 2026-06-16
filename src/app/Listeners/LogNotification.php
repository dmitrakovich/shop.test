<?php

namespace App\Listeners;

use App\Events\Notifications\NotificationSkipped;
use App\Notifications\AbstractSmsTraffic;
use App\Services\Sms\SmsLogService;
use Illuminate\Notifications\Client\Response\SmsTrafficResponse;
use Illuminate\Notifications\Events\NotificationSent;

class LogNotification
{
    public function __construct(private readonly SmsLogService $smsLogService) {}

    public function handle(NotificationSent|NotificationSkipped $event): void
    {
        $notification = $event->notification;
        if (!$notification instanceof AbstractSmsTraffic) {
            return;
        }

        $response = $event instanceof NotificationSent && $event->response instanceof SmsTrafficResponse
            ? $event->response
            : null;

        $this->smsLogService->logNotification(
            notification: $notification,
            notifiable: $event->notifiable,
            response: $response,
            skipped: $event instanceof NotificationSkipped,
        );
    }
}
