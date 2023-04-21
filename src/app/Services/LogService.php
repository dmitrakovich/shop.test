<?php

namespace App\Services;

use App\Models\Logs\InventoryLog;
use App\Models\Logs\SmsLog;

/**
 * Class LogService
 */
class LogService
{
    /**
     * Status for skipped sms messages
     */
    const SMS_SKIPPED_KEY = 'skipped';

    /**
     * Log sms notification
     */
    public function logSms(
        string $phone,
        string $text,
        string $route,
        ?int $adminId = null,
        ?int $userId = null,
        ?int $orderId = null,
        ?int $mailingId = null,
        ?string $status = null,
    ): SmsLog {
        $log = new SmsLog;
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
            $logData[$productId]['action'] = InventoryLog::ACTION_RESTORE;
        }
        foreach ($deleteProducts as $productId) {
            $logData[$productId]['product_id'] = $productId;
            $logData[$productId]['action'] = InventoryLog::ACTION_DELETE;
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
            $data['action'] ??= InventoryLog::ACTION_UPDATE;
            $data['added_sizes'] ??= null;
            $data['removed_sizes'] ??= null;
            $data['created_at'] = $now;
        }

        InventoryLog::insert($logData);
    }
}
