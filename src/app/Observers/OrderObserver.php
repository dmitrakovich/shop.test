<?php

namespace App\Observers;

use App\Events\OrderStatusChanged;
use App\Models\Logs\OrderActionLog;
use App\Models\Orders\Order;
use App\Models\User\User;
use App\Services\LogService;
use Illuminate\Support\Arr;

class OrderObserver
{
    /**
     * Handle the Order "saving" event.
     */
    public function saving(Order $order): void
    {
        $this->logOrderChanges($order);

        if ($order->isDirty('status_key')) {
            $order->status_updated_at = now();
            event(new OrderStatusChanged($order, $order->getOriginal('status_key')));
        }
    }

    /**
     * Handle the Order "saved" event.
     */
    public function saved(Order $order): void
    {
        $this->logOrderChanges($order);
    }

    /**
     * Log order changes
     */
    private function logOrderChanges(Order $order): void
    {
        if (empty($order->id) || $order->isLoggingDone || $order->isClean()) {
            return;
        }

        $logService = new LogService();

        if ($order->wasRecentlyCreated) {
            $logService->logOrderAction($order->id, empty($order->admin_id) ? 'Заказ принят' : 'Заказ создан');
            $order->isLoggingDone = true;
            return;
        }

        $newAdmin = $order->getAdmin();
        $prevAdmin = $order->getPrevAdmin();

        if ($newAdmin->id !== $prevAdmin->id) {
            $logService->logOrderAction($order->id, "Заказ передан от {$prevAdmin->name} к {$newAdmin->name}");
        }

        if ($order->isDirty('status_key')) {
            if ($order->status_key === 'in_work') {
                $logService->logOrderAction($order->id, "Заказ взят в работу менеджером {$newAdmin->name}");
            }

            $logService->logOrderAction($order->id, "Статус заказа изменился с “{$order->getOriginal('status_key')}” на “{$order->status_key}”");
        }

        if (!empty($order->user_id) && $order->isDirty('user_id')) {
            if ($user = User::query()->find($order->user_id)) {
                $logService->logOrderAction($order->id, "К заказу привязан клиент {$user->phone}, id: {$user->id}");
            }
        }

        $trackedFields = OrderActionLog::getTrackedFields();
        $newValues = Arr::only($order->getDirty(), array_keys($trackedFields));
        $changes = [];
        foreach ($newValues as $field => $newValue) {
            $changes[] = "В поле “{$trackedFields[$field]}” изменено значение “{$order->getOriginal($field)}” на “{$newValue}”";
        }
        if (!empty($changes)) {
            $logService->logOrderAction($order->id, implode(PHP_EOL, $changes));
        }

        $order->isLoggingDone = true;
        return;
    }
}
