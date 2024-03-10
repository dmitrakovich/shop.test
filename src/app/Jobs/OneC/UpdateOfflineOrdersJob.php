<?php

namespace App\Jobs\OneC;

use App\Jobs\AbstractJob;
use App\Models\OneC\OfflineOrder as OfflineOrder1C;
use App\Models\Orders\OfflineOrder;
use App\Models\Size;
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
                // if refund, найти, отаравить сообщение с помощью бота в ТГ и обновить дату в оригинальной записи
                continue;
            }

            $offlineOrder = new OfflineOrder([
                'receipt_number' => $order->SP6098,
                'stock_id' => $order->stock->id,
                'product_id' => $order->product?->id,
                'size_id' => $order->size?->id ?? Size::ONE_SIZE_ID,
                'price' => $order->SP6101,
                'count' => $order->SP6099,
                // 'user_id' => $order,
                'user_phone' => $order->SP6102,
                'sold_at' => $order->getSoldAtDateTime(),
                // 'returned_at' => $order,
            ]);

            $offlineOrder->save();
        }

        // dd($orders);

        // найти все зависимости
        // создать пользователя
        // создать дисконтную карту
        //
    }

    private function getLatestCode(): int
    {
        $receiptNumber = OfflineOrder::query()->latest('id')->value('receipt_number');

        return OfflineOrder1C::getLatestCodeByReceipNumber($receiptNumber);
    }

    /**
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
}
