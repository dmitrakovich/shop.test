<?php

namespace App\Http\Resources\Order;

use App\Enums\Payment\OnlinePaymentStatusEnum;
use App\Models\Payments\OnlinePayment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Orders\Order
 */
class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'total_price' => $this->getTotalPrice(),
            'currency_code' => $this->currency,
            'delivery_name' => $this->delivery?->name,
            'payment_name' => $this->payment?->name,
            'user_address' => $this->user_addr,
            'status' => [
                'key' => $this->status->getOldKey(),
                'value' => $this->status->value,
                'name_for_user' => $this->status->getLabelForClient(),
            ],
            'track' => $this->when((bool)$this->track, fn () => [
                'number' => $this->track->track_number,
                'link' => $this->track->track_link,
            ], []),
            'onlinePayments' => $this->getOnlinePayments(),
            'items' => OrderItemResource::collection($this->items),
            'created_at' => $this->created_at,
        ];
    }

    private function getOnlinePayments(): array
    {
        return $this->onlinePayments
            ->where('last_status_enum_id', OnlinePaymentStatusEnum::PENDING->value)
            ->map(fn (OnlinePayment $onlinePayment) => [
                'link' => $onlinePayment->link,
                'payment_num' => $onlinePayment->payment_num,
            ])
            ->toArray();
    }
}
