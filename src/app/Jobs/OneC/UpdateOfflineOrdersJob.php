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
    const NEW_ORDERS_LIMIT = 5;

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
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $latestCode = $this->getLatestCode();
        $orders = $this->getNewOrders($latestCode);

        foreach ($orders as $order) {
            if ($order->isReturn()) {
                // if refund, найти, отправить сообщение с помощью бота в ТГ и обновить дату в оригинальной записи
                // 'returned_at' => $order,
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
