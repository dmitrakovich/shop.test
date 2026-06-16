<?php

namespace App\Services;

use App\Models\Logs;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Support\Facades\Auth;

/**
 * Class LogService
 */
class LogService
{
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
            $logData[$productId]['action'] = Logs\InventoryLog::ACTION_RESTORE;
        }
        foreach ($deleteProducts as $productId) {
            $logData[$productId]['product_id'] = $productId;
            $logData[$productId]['action'] = Logs\InventoryLog::ACTION_DELETE;
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
            $data['action'] ??= Logs\InventoryLog::ACTION_UPDATE;
            $data['added_sizes'] ??= null;
            $data['removed_sizes'] ??= null;
            $data['created_at'] = $now;
        }

        Logs\InventoryLog::query()->insert($logData);
    }

    /**
     * Log order changes data
     */
    public function logOrderAction(int $orderId, string $action): Logs\OrderActionLog
    {
        $user = Auth::user();

        $log = new Logs\OrderActionLog();
        $log->order_id = $orderId;
        $log->admin_id = $user instanceof Administrator ? $user->id : null;
        $log->action = $action;
        $log->save();

        return $log;
    }
}
