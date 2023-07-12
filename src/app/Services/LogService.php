<?php

namespace App\Services;

use App\Models\Logs as LogModel;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Support\Facades\Auth;

/**
 * Class LogService
 */
class LogService
{
    /**
     * Status for skipped sms messages
     *
     * @todo move to LogModel\SmsLog
     */
    const SMS_SKIPPED_KEY = 'skipped';

    /**
     * Log sms notification
     */
    public function logSms(
        string $phone,
        string $text,
        string $route,
        int $adminId = null,
        int $userId = null,
        int $orderId = null,
        int $mailingId = null,
        string $status = null,
    ): LogModel\SmsLog {
        $log = new LogModel\SmsLog;
        $log->phone = $phone;
        $log->text = $text;
        $log->route = $route;
        $log->admin_id = $adminId;
        $log->user_id = $userId;
        $log->order_id = $orderId;
        $log->mailing_id = $mailingId;
        $log->status = $status;
        $log->save();

        return $log;
    }

    /**
     * Log availability update data
     */
    public function logAvailabilityUpdate(
        array $restoreProducts,
        array $deleteProducts,
        array $addSizes,
        array $deleteSizes
    ): void {
        $logData = [];
        foreach ($restoreProducts as $productId) {
            $logData[$productId]['product_id'] = $productId;
            $logData[$productId]['action'] = LogModel\InventoryLog::ACTION_RESTORE;
        }
        foreach ($deleteProducts as $productId) {
            $logData[$productId]['product_id'] = $productId;
            $logData[$productId]['action'] = LogModel\InventoryLog::ACTION_DELETE;
        }
        foreach ($addSizes as $productId => $sizes) {
            $logData[$productId]['product_id'] = $productId;
            $logData[$productId]['added_sizes'] = json_encode(array_values($sizes));
        }
        foreach ($deleteSizes as $productId => $sizes) {
            $logData[$productId]['product_id'] = $productId;
            $logData[$productId]['removed_sizes'] = json_encode(array_values($sizes));
        }

        $now = now();
        foreach ($logData as &$data) {
            $data['action'] ??= LogModel\InventoryLog::ACTION_UPDATE;
            $data['added_sizes'] ??= null;
            $data['removed_sizes'] ??= null;
            $data['created_at'] = $now;
        }

        LogModel\InventoryLog::insert($logData);
    }

    /**
     * Log order changes data
     */
    public function logOrderAction(int $orderId, string $action): LogModel\OrderActionLog
    {
        $user = Auth::user();

        $log = new LogModel\OrderActionLog();
        $log->order_id = $orderId;
        $log->admin_id = $user instanceof Administrator ? $user->id : null;
        $log->action = $action;
        $log->save();

        return $log;
    }
}
