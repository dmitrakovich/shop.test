<?php

namespace App\Jobs\OneC;

use App\Jobs\AbstractJob;
use App\Models\OneC\OfflineOrder as OfflineOrder1C;
use App\Models\Orders\OfflineOrder;
use App\Models\Size;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Collection;

class UpdateOfflineOrdersJob extends AbstractJob
{
    const NEW_ORDERS_LIMIT = 100;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 500;

    /**
     * @var array
     */
    protected $contextVars = ['usedMemory'];

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $latestCode = $this->getLatestCode();
        $orders = $this->getNewOrders($latestCode);
        $returnOrders = $this->getOrdersForReturn($orders);

        foreach ($orders as $order) {
            if (isset($returnOrders[$order->SP6098])) {
                $returnOrder = $returnOrders[$order->SP6098];
                $returnOrder->update(['returned_at' => $order->getReturnedAtDateTime()]);

                //! отправить сообщение с помощью бота в ТГ
                continue;
            }

            if ($order->isReturn()) {
                \Sentry\captureMessage(
                    "Return order without sold order in DB, receipt: {$order->SP6098}",
                    \Sentry\Severity::warning()
                );

                continue;
            }

            $offlineOrder = new OfflineOrder([
                'receipt_number' => $order->SP6098,
                'stock_id' => $order->stock->id,
                'product_id' => $order->product?->id,
                'size_id' => $order->size?->id ?? Size::ONE_SIZE_ID,
                'price' => $order->SP6101,
                'count' => $order->SP6099,
                'sku' => $order->SP6093,
                'user_id' => $this->findOrCreateUser($order)?->id,
                'user_phone' => $order->SP6102,
                'sold_at' => $order->getSoldAtDateTime(),
            ]);

            $offlineOrder->save();
        }
    }

    /**
     * Get the latest code from the offline orders.
     */
    private function getLatestCode(): int
    {
        $receiptNumber = OfflineOrder::query()->latest('id')->value('receipt_number');

        return OfflineOrder1C::getLatestCodeByReceiptNumber($receiptNumber);
    }

    /**
     * Get new offline orders based on the latest code.
     *
     * @return Collection|OfflineOrder1C[]
     */
    private function getNewOrders(int $latestCode): Collection
    {
        return OfflineOrder1C::query()
            ->with(['stock', 'product', 'size'])
            ->where('CODE', '>', $latestCode)
            ->limit(self::NEW_ORDERS_LIMIT)
            ->orderBy('CODE')
            ->get();
    }

    /**
     * Get the offline orders eligible for return.
     *
     * @param  Collection|OfflineOrder1C[]  $orders
     * @return Collection|OfflineOrder[]
     */
    private function getOrdersForReturn(Collection $orders): Collection
    {
        $receipts = $orders->filter(fn (OfflineOrder1C $order) => $order->isReturn())->pluck('SP6098')->toArray();

        return OfflineOrder::query()->whereIn('receipt_number', $receipts)->get()->keyBy('receipt_number');
    }

    /**
     * Find or create a user based on the offline order.
     */
    private function findOrCreateUser(OfflineOrder1C $order): ?User
    {
        $phone = $order->getFormattedPhone();
        if (!$phone) {
            return null;
        }
        /** @var User $user */
        $user = User::query()
            ->where('phone', $phone)
            ->orWhere('discount_card_number', $order->SP6089)
            ->firstOrCreate([], [
                'phone' => $phone,
                'discount_card_number' => $order->SP6089,
                'first_name' => $order->DESCR,
            ]);

        if (!$user->wasRecentlyCreated) {
            $user->update(['discount_card_number' => $order->SP6089]);
        }

        return $user;
    }
}
