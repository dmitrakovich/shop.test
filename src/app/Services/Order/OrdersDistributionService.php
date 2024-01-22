<?php

namespace App\Services\Order;

use App\Admin\Models\Administrator;
use App\Models\Config;
use App\Models\Logs\OrderDistributionLog;
use App\Models\Orders\Order;
use App\Models\Orders\OrderAdminComment;
use App\Models\WorkSchedule;
use Carbon\Carbon;

class OrdersDistributionService
{
    /**
     * Distributes an order.
     *
     * @param  Order  $order  The order to be distributed.
     * @return Order The distributed order.
     */
    public function distributeOrder(Order $order): Order
    {
        $distribOrderSetup = Config::findCacheable('distrib_order_setup');
        $distributeOrderActive = $distribOrderSetup['active'] ?? false;
        if ($distributeOrderActive) {
            $isEvenDay = date('d') % 2 == 0;
            $timeFromKey = 'time_from_' . ($isEvenDay ? 'even' : 'odd');
            $timeToKey = 'time_to_' . ($isEvenDay ? 'even' : 'odd');
            $logCount = OrderDistributionLog::where('created_at', '>', Carbon::today())->count();
            $prevDistribution = OrderDistributionLog::where('created_at', '>', Carbon::today())->orderBy('id', 'desc')->first();
            $workSchedule = WorkSchedule::where('date', date('Y-m-d'))->get();
            $workScheduleAdminIds = $workSchedule->pluck('admin_user_id', 'admin_user_id')->toArray();
            $scheduleSetup = $distribOrderSetup['schedule'] ?? [];
            $scheduleSetup = array_filter($scheduleSetup, function ($item) use ($workScheduleAdminIds, $timeFromKey, $timeToKey) {
                return
                    isset($workScheduleAdminIds[$item['admin_user_id']]) &&
                    isset($item[$timeFromKey]) &&
                    isset($item[$timeToKey]) &&
                    strtotime($item[$timeFromKey]) <= strtotime('now') &&
                    strtotime($item[$timeToKey]) >= strtotime('now');
            });
            usort($scheduleSetup, function ($a, $b) use ($timeToKey) {
                $timeA = strtotime($a[$timeToKey]);
                $timeB = strtotime($b[$timeToKey]);
                if ($timeA == $timeB) {
                    return $a['admin_user_id'] < $b['admin_user_id'] ? -1 : 1;
                }

                return $timeA < $timeB ? -1 : 1;
            });
            $distributeOrderByLink = $this->distributeOrderByLink($order);
            $distributeAdditionalOrder = $this->distributeAdditionalOrder($order);
            if (
                !$distributeOrderByLink &&
                !$distributeAdditionalOrder &&
                !empty($scheduleSetup)
            ) {
                $loopNumber = floor($logCount / count($scheduleSetup)) + 1;

                $prevKey = array_search($prevDistribution->admin_user_id ?? null, array_column($scheduleSetup, 'admin_user_id'));
                $currentKey = ($prevKey === false) ? key($scheduleSetup) : ($prevKey + 1) % count($scheduleSetup);
                $currentSchedule = $scheduleSetup[$currentKey];

                $order->distributionLogs()->create([
                    'admin_user_id' => $currentSchedule['admin_user_id'],
                    'action' => "по очереди №{$loopNumber}",
                ]);
                $order->update([
                    'admin_id' => $currentSchedule['admin_user_id'],
                ]);
            }
        }

        return $order;
    }

    /**
     * Distribute additional order.
     *
     * @param  Order  $order  The order to distribute.
     * @return bool Returns true if the additional order was successfully distributed, false otherwise.
     */
    public function distributeAdditionalOrder(Order $order): bool
    {
        $userPrevOrder = Order::where(function ($query) {
            return $query->whereNotIn('status_key', [
                'complete',
                'canceled',
                'sent',
                'fitting',
                'return',
                'return_fitting',
                'partial_complete'
            ])->orWhere('created_at', '>', date('Y-m-d H:i:s', strtotime('-7 days')));
        })->where(fn ($query) => $query->whereNotNull('user_id')->where('user_id', $order->user_id))
            ->where('id', '!=', $order->id)
            ->orderBy('id', 'desc')
            ->first();
        if ($userPrevOrder) {
            if ($order->admin_id !== $userPrevOrder->admin_id) {
                $addOrderText = "Дозаказ к заказу {$userPrevOrder->id}";
                $order->update([
                    'admin_id' => $userPrevOrder->admin_id,
                ]);
                $order->distributionLogs()->create([
                    'admin_user_id' => $order->admin_id,
                    'action' => $addOrderText,
                ]);
                OrderAdminComment::create([
                    'order_id' => $order->id,
                    'comment' => $addOrderText,
                ]);
            }

            return true;
        }

        return false;
    }

    /**
     * A function to distribute an order based on the provided link.
     *
     * @param  Order  $order  The order object to be distributed.
     * @return bool Returns true if the order is successfully distributed, false otherwise.
     */
    public function distributeOrderByLink(Order $order): bool
    {
        $orderUtmContent = $order->utm_content ?? null;
        if (!empty($order->utm_campaign) && $order->utm_campaign === 'manager' && $orderUtmContent) {
            $admin = Administrator::where('username', $orderUtmContent)->first();
            if (isset($admin->id)) {
                $order->distributionLogs()->create([
                    'admin_user_id' => $admin->id,
                    'action' => 'по ссылке',
                ]);
                $order->update([
                    'admin_id' => $admin->id,
                ]);

                return true;
            }
        }

        return false;
    }
}
