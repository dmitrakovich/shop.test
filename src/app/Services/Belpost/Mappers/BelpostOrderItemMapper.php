<?php

namespace App\Services\Belpost\Mappers;

use App\Enums\Belpost\BelpostNotification;
use App\Enums\Belpost\BelpostPostalDeliveryType;
use App\Enums\Order\OrderItemStatus;
use App\Libraries\Belpost\Exceptions\BelpostApiException;
use App\Models\Orders\Batch;
use App\Models\Orders\Order;
use App\Services\Belpost\Recipient\BelpostRecipientService;
use App\Services\Belpost\Support\BelpostPhoneNormalizer;

/**
 * Maps a local {@see Order} to the Belpost batch-mailing item payload.
 */
class BelpostOrderItemMapper
{
    public function __construct(
        private readonly BelpostRecipientService $recipientService,
        private readonly BelpostPhoneNormalizer $phoneNormalizer,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toCreatePayload(Order $order, ?Batch $batch = null): array
    {
        $order->loadMissing(['user.lastAddress', 'track']);

        $notification = $this->resolveNotification($order, $batch);
        $email = $this->resolveRecipientEmail($order);
        $phone = $this->resolveRecipientPhone($order);

        $this->assertRecipientContact($order, $notification, $email, $phone);

        $recipientForeignId = $this->recipientService->ensureForeignId($order);

        $payload = [
            'foreign_id' => (string)$order->id,
            'weight' => (int)max((int)ceil($order->weight ?: 1), 1),
            'category' => 0,
            'notification' => $notification,
            'recipient_foreign_id' => $recipientForeignId,
            'recipient_phone' => $phone,
        ];

        $addons = [];

        if ($notification === BelpostNotification::Electronic->value) {
            $addons['email'] = $email;
            $addons['phone'] = $phone;
        }

        $s10code = $this->resolveS10Code($order);
        if ($s10code !== null) {
            $payload['s10code'] = $s10code;
        }

        $cod = $this->resolveCashOnDelivery($order);
        if ($cod > 0) {
            $addons['cash_on_delivery'] = round($cod, 2);
        }

        if ($addons !== []) {
            $payload['addons'] = $addons;
        }

        return $payload;
    }

    private function resolveS10Code(Order $order): ?string
    {
        $code = trim((string)($order->belpost_s10code ?? ''));
        if ($code !== '') {
            return $code;
        }

        $track = $order->track;
        if ($track === null) {
            return null;
        }

        $trackNumber = trim((string)($track->track_number ?? ''));

        return $trackNumber !== '' ? $trackNumber : null;
    }

    private function resolveRecipientEmail(Order $order): string
    {
        $email = trim((string)($order->email ?: $order->user?->email ?: ''));

        if ($email === '') {
            $email = (string)config('belpost.defaults.fallback_recipient_email', config('app.email'));
        }

        return mb_strtolower($email);
    }

    private function resolveRecipientPhone(Order $order): ?string
    {
        return $this->phoneNormalizer->normalize($order->phone)
            ?? ($order->user ? $this->phoneNormalizer->normalize($order->user->phone) : null);
    }

    private function assertRecipientContact(Order $order, int $notification, string $email, ?string $phone): void
    {
        if ($email === '') {
            throw new BelpostApiException("Order #{$order->id}: recipient e-mail is required for Belpost API.");
        }

        if ($notification !== BelpostNotification::Electronic->value) {
            return;
        }

        if ($phone === null || strlen($phone) < 9) {
            throw new BelpostApiException(
                "Order #{$order->id}: recipient phone is required for electronic notification (min. 9 digits)."
            );
        }
    }

    /**
     * E-commerce parcel types only accept notification values 1, 2, 5.
     * COD with notification "none" is upgraded to electronic.
     */
    private function resolveNotification(Order $order, ?Batch $batch): int
    {
        $configured = BelpostNotification::tryFromConfigured(
            config('belpost.defaults.notification')
        ) ?? BelpostNotification::Electronic;

        $postalType = $this->resolvePostalDeliveryType($batch);

        if ($this->isEcommercePostalType($postalType)) {
            if (in_array($configured->value, BelpostNotification::ecommerceValues(), true)) {
                return $configured->value;
            }

            return BelpostNotification::Electronic->value;
        }

        if ($this->resolveCashOnDelivery($order) > 0 && $configured === BelpostNotification::None) {
            return BelpostNotification::Electronic->value;
        }

        return $configured->value;
    }

    private function resolvePostalDeliveryType(?Batch $batch): string
    {
        $type = $batch?->postal_delivery_type;

        if ($type instanceof BelpostPostalDeliveryType) {
            return $type->value;
        }

        if (is_string($type) && $type !== '') {
            return $type;
        }

        return (string)config('belpost.defaults.postal_delivery_type', BelpostPostalDeliveryType::EcommerceStandard->value);
    }

    private function isEcommercePostalType(string $postalType): bool
    {
        return str_starts_with($postalType, 'ecommerce_');
    }

    private function resolveCashOnDelivery(Order $order): float
    {
        $paymentIds = config('belpost.cod_payment_ids', [1, 4]);

        if (!in_array($order->payment_id, $paymentIds, true)) {
            return 0;
        }

        $order->loadMissing([
            'itemsExtended' => fn ($query) => $query
                ->whereIn('status', OrderItemStatus::departureStatuses())
                ->with('installment'),
            'onlinePayments',
        ]);

        return $order->getTotalCODSum();
    }
}
