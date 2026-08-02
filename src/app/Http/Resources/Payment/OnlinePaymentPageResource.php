<?php

namespace App\Http\Resources\Payment;

use App\Enums\Payment\OnlinePaymentMethodEnum;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin \App\Models\Payments\OnlinePayment
 */
class OnlinePaymentPageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isErip = $this->method_enum_id === OnlinePaymentMethodEnum::ERIP;

        return [
            'payment_num' => $this->payment_num,
            'order_id' => $this->order_id,
            'order_date' => $this->order?->created_at?->format('d.m.Y'),
            'amount' => $this->amount,
            'currency_code' => $this->currency_code,
            'qr_code_url' => $this->qrCodeUrl(),
            'epos_account' => $isErip
                ? config('hgrosh.serviceproviderid') . '-1-' . $this->payment_num
                : null,
            'link_code' => $this->link_code,
        ];
    }

    private function qrCodeUrl(): ?string
    {
        if (!$this->qr_code) {
            return null;
        }

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('public');

        return $disk->url($this->qr_code);
    }
}
