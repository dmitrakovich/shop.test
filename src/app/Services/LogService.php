<?php

namespace App\Services;

use App\Models\Logs\SmsLog;

/**
 * Class LogService
 */
class LogService
{
    /**
     * LogService constructor.
     */
    public function __construct(private SmsLog $smsLog)
    {
    }

    /**
     * Log sms notification
     */
    public function logSms(
        string $phone,
        string $text,
        string $route,
        ?int $adminId = null,
        ?int $orderId = null,
        ?string $status = null,
    ): SmsLog {
        $log = $this->smsLog->newInstance();
        $log->phone = $phone;
        $log->text = $text;
        $log->route = $route;
        $log->admin_id = $adminId;
        $log->order_id = $orderId;
        $log->status = $status;
        $log->save();

        return $log;
    }
}
