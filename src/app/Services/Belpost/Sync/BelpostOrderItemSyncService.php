<?php

namespace App\Services\Belpost\Sync;

use App\Enums\DeliveryTypeEnum;
use App\Libraries\Belpost\Exceptions\BelpostApiException;
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

        $itemId = Arr::get($item, 'id')
            ?? Arr::get($item, 'item_id')
            ?? Arr::get($item, 'list_item_id');

        if ($itemId !== null && $itemId !== '') {
            $itemId = (int)$itemId;
        } elseif ($order->belpost_item_id !== null) {
            $itemId = (int)$order->belpost_item_id;
        } else {
            throw new BelpostApiException(
                "Order #{$order->id}: Belpost API response did not contain item id.",
            );
        }

        $order->update([
            'belpost_item_id' => $itemId,
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
