<?php

namespace App\Services\Sms;

use App\Enums\Sms\SmsDeliveryStatus;
use App\Enums\Sms\SmsRoute;
use App\Models\Logs\SmsLog;
use App\Models\Orders\Order;
use App\Models\User\User;
use App\Notifications\AbstractSmsTraffic;
use Illuminate\Notifications\Client\Response\SmsTrafficResponse;

class SmsLogService
{
    public function log(
        string $phone,
        string $text,
        string $route,
        ?int $adminId = null,
        ?int $userId = null,
        ?int $orderId = null,
        ?int $mailingId = null,
        ?string $status = null,
        ?string $smsId = null,
        ?SmsTrafficResponse $sendResponse = null,
    ): SmsLog {
        if ($sendResponse !== null) {
            if (self::sendResponseIsSuccessful($sendResponse)) {
                $smsId = $sendResponse->getSmsId();
                $status = self::sendResponseDescription($sendResponse, SmsDeliveryStatus::QueuedOneMessage->value);
            } else {
                $status = self::sendResponseDescription($sendResponse, 'Сообщение не принято шлюзом');
            }
        }

        $log = new SmsLog();
        $log->phone = $phone;
        $log->text = $text;
        $log->route = SmsRoute::from($route);
        $log->admin_id = $adminId;
        $log->user_id = $userId;
        $log->order_id = $orderId;
        $log->mailing_id = $mailingId;
        $log->status = SmsDeliveryStatus::resolve($status);
        $log->sms_id = $smsId;
        $log->save();

        return $log;
    }

    public function logNotification(
        AbstractSmsTraffic $notification,
        mixed $notifiable,
        ?SmsTrafficResponse $response = null,
        bool $skipped = false,
    ): ?SmsLog {
        $context = $this->notificationContext($notification, $notifiable);
        if ($context === null) {
            return null;
        }

        if ($skipped) {
            return $this->log(...$context, status: SmsDeliveryStatus::Skipped->value);
        }

        if ($response === null) {
            return null;
        }

        return $this->log(...$context, sendResponse: $response);
    }

    /**
     * @return array{
     *     phone: string,
     *     text: string,
     *     route: string,
     *     adminId: null,
     *     userId: int|null,
     *     orderId: int|null,
     *     mailingId: int|null,
     * }|null
     */
    private function notificationContext(AbstractSmsTraffic $notification, mixed $notifiable): ?array
    {
        $route = $notification->getRoute();
        if ($route === null) {
            return null;
        }

        return [
            'phone' => $notifiable->routeNotificationFor('smstraffic', $notification),
            'text' => $notification->getContent(),
            'route' => $route,
            'adminId' => null,
            'userId' => $notifiable instanceof User ? $notifiable->id : null,
            'orderId' => $notifiable instanceof Order ? $notifiable->id : null,
            'mailingId' => $notification->getMailingId(),
        ];
    }

    private static function sendResponseIsSuccessful(SmsTrafficResponse $response): bool
    {
        return !$response->hasError() && $response->getSmsId() !== null;
    }

    private static function sendResponseDescription(SmsTrafficResponse $response, string $fallback): string
    {
        if ($response->hasError()) {
            return $response->getErrorMessage();
        }

        return trim($response->getDescription() ?? '') ?: $fallback;
    }
}
