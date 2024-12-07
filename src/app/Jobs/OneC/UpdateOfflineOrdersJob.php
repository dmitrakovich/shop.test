<?php

namespace App\Jobs\OneC;

use App\Enums\User\OrderType;
use App\Jobs\AbstractJob;
use App\Models\Bots\Telegram\TelegramChat;
use App\Models\Brand;
use App\Models\Logs\OrderItemStatusLog;
use App\Models\OneC\OfflineOrder as OfflineOrder1C;
use App\Models\Orders\OfflineOrder;
use App\Models\Orders\OrderItem;
use App\Models\Product;
use App\Models\Stock;
use App\Models\User\User;
use App\Notifications\OrderItemInventoryNotification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Context;
use Sentry\Severity;

use function Sentry\captureMessage;

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
     * Stock models collection
     *
     * @var Collection<Stock>
     */
    private Collection $stocks;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->setStocks();

        [$newReturnOrders, $newSaleOrders] = $this->getNewOrders()->partition(
            fn (OfflineOrder1C $order) => $order->isReturn()
        );

        foreach ($newSaleOrders as $order) {
            Context::add('1C sale order', $order->attributesToArray());

            $offlineOrder = OfflineOrder::query()->create([
                'one_c_id' => $order->CODE,
                'receipt_number' => $order->SP6098,
                'stock_id' => $order->stock->id,
                'product_id' => $order->product?->id,
                'one_c_product_id' => $order->SP6092,
                'size_id' => $order->getSizeId(),
                'price' => $order->SP6101,
                'count' => $order->SP6099,
                'sku' => $order->SP6093,
                'user_id' => $this->findOrCreateUser($order)?->id,
                'user_phone' => $order->SP6102,
                'sold_at' => $order->getSoldAtDateTime(),
            ]);

            // todo: если продажа с ИМ, переводить статус соответствующего заказа

            if (!$order->isOnline()) {
                $this->notify($offlineOrder);
            }
        }
        Context::forget('1C sale order');

        $returnOrders = $this->getOrdersForReturn($newReturnOrders);
        foreach ($newReturnOrders as $order) {
            Context::add('1C return order', $order->attributesToArray());

            $orderItemKey = $this->generateKeyForCompare($order);
            if (isset($returnOrders[$orderItemKey])) {
                $returnOrder = $returnOrders[$orderItemKey];
                $returnOrder->update(['returned_at' => $order->getReturnedAtDateTime()]);
            } else {
                captureMessage('Return 1C order without sold order in DB', Severity::warning());
            }
        }
    }

    /**
     * Get the latest code from the offline orders.
     */
    private function getLatestCode(): int
    {
        return (int)OfflineOrder::query()->latest('id')->value('one_c_id');
    }

    /**
     * Get new offline orders based on the latest code.
     *
     * @return Collection|OfflineOrder1C[]
     */
    private function getNewOrders(): Collection
    {
        return OfflineOrder1C::query()
            ->with(['stock', 'product', 'size', 'discountCard'])
            ->where('CODE', '>', $this->getLatestCode())
            ->limit(self::NEW_ORDERS_LIMIT)
            ->orderBy('CODE')
            ->get();
    }

    /**
     * Get the offline orders eligible for return.
     *
     * @param  Collection|OfflineOrder1C[]  $newReturnOrders
     * @return Collection|OfflineOrder[]
     */
    private function getOrdersForReturn(Collection $newReturnOrders): Collection
    {
        return OfflineOrder::query()
            ->with(['product'])
            ->whereIn('receipt_number', $newReturnOrders->pluck('SP6098')->toArray())
            ->get()
            ->keyBy(fn (OfflineOrder $offlineOrder) => $this->generateKeyForCompare($offlineOrder));
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
                'first_name' => $order->SP6130,
                'last_name' => $order->SP6129,
                'patronymic_name' => $order->SP6131,
                'birth_date' => $order->discountCard?->SP3970,
            ]);

        if (!$user->wasRecentlyCreated) {
            $user->update(['discount_card_number' => $order->SP6089]);
        }

        $user->metadata()->updateOrCreate([], [
            'last_order_type' => OrderType::OFFLINE,
            'last_order_date' => $order->SP6107,
        ]);

        return $user;
    }

    /**
     * Generate unique key for order item id
     */
    private function generateKeyForCompare(OfflineOrder|OfflineOrder1C $offlineOrder): string
    {
        if ($offlineOrder instanceof OfflineOrder) {
            return "{$offlineOrder->receipt_number}|{$offlineOrder->one_c_product_id}|{$offlineOrder->size_id}";
        }
        if ($offlineOrder instanceof OfflineOrder1C) {
            return "{$offlineOrder->SP6098}|{$offlineOrder->SP6092}|{$offlineOrder->getSizeId()}";
        }
    }

    /**
     * Notify the Telegram chat about a offline order.
     */
    private function notify(OfflineOrder $offlineOrder): void
    {
        if (!$chat = $this->getChatByStockId($offlineOrder->stock_id)) {
            return;
        }

        $notification = new OrderItemStatusLog(['stock_id' => $offlineOrder->stock_id]);
        $orderItem = (new OrderItem(['size_id' => $offlineOrder->size_id, 'status_key' => 'complete']))
            ->setRelation('product', $this->getProductFromOfflineOrder($offlineOrder))
            ->setRelation('inventoryNotification', $notification);

        $chat->notifyNow(new OrderItemInventoryNotification($orderItem));
    }

    /**
     * Set the stocks for the current context.
     */
    private function setStocks(): void
    {
        $this->stocks = Stock::with(['groupChat'])
            ->get(['id', 'group_chat_id'])
            ->keyBy('id');
    }

    /**
     * Get the Telegram chat model associated with the specified stock ID.
     */
    private function getChatByStockId(int $stockId): ?TelegramChat
    {
        return $this->stocks[$stockId]?->groupChat;
    }

    private function getProductFromOfflineOrder(OfflineOrder $offlineOrder): Product
    {
        if ($offlineOrder->product) {
            return $offlineOrder->product;
        }

        $product = new Product([
            'id' => $offlineOrder->one_c_product_id,
            'sku' => $offlineOrder->sku,
        ]);
        $product->setRelation('brand', new Brand(['name' => 'Нет на сайте -']));

        return $product;
    }
}
