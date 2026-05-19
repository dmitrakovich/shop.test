<?php

namespace App\Services\Belpost\Sync;

use App\Enums\DeliveryTypeEnum;
use App\Models\Orders\Order;
use App\Models\Orders\OrderTrack;
use Illuminate\Support\Arr;

class BelpostOrderItemSyncService
{
    /**
     * @param  array<string, mixed>  $item
     */
    public function applyItemResponse(Order $order, array $item): Order
    {
        $s10code = Arr::get($item, 's10code');

        $order->update([
            'belpost_item_id' => Arr::get($item, 'id', $order->belpost_item_id),
            'belpost_s10code' => $s10code,
        ]);

        if ($s10code) {
            OrderTrack::query()->updateOrCreate(
                [
                    'order_id' => $order->id,
                    'delivery_type_enum' => DeliveryTypeEnum::BELPOST,
                ],
                [
                    'track_number' => $s10code,
                    'track_link' => 'https://belpost.by/Otsleditotpravleniye?number=' . $s10code,
                ],
            );
        }

        return $order->refresh();
    }

    public function clearBelpostFields(Order $order): void
    {
        $order->update([
            'belpost_item_id' => null,
            'belpost_s10code' => null,
        ]);
    }
}
