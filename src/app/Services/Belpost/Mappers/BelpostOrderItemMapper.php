<?php

namespace App\Services\Belpost\Mappers;

use App\Enums\Belpost\BelpostNotification;
use App\Enums\Belpost\BelpostPostalDeliveryType;
use App\Enums\DeliveryTypeEnum;
use App\Enums\Order\OrderItemStatus;
use App\Libraries\Belpost\Exceptions\BelpostApiException;
use App\Models\Orders\Batch;
use App\Models\Orders\Order;
use App\Models\Orders\OrderTrack;
use App\Services\Belpost\Recipient\BelpostRecipientService;
use App\Services\Belpost\Support\BelpostPhoneNormalizer;
use App\Services\Belpost\Support\BelpostS10CodeValidator;

/**
 * Maps a local {@see Order} to the Belpost batch-mailing item payload.
 */
class BelpostOrderItemMapper
{
    public function __construct(
        private readonly BelpostRecipientService $recipientService,
        private readonly BelpostPhoneNormalizer $phoneNormalizer,
        private readonly BelpostS10CodeValidator $s10CodeValidator,
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
            'weight' => $order->getWeightInGrams(),
            'category' => $this->resolveItemCategory($batch),
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

        $addons = $this->applyCashOnDeliveryAddons($order, $batch, $addons);
        $addons = $this->applyBatchAddonsForDeclaredOrPartialReceipt($order, $batch, $addons);

        if ($addons !== []) {
            $payload['addons'] = $addons;
        }

        return $payload;
    }

    /**
     * Belarus Post: COD above {@see config belpost.defaults.max_cod_without_declared_value} BYN
     * requires batch declared value and matching item `addons.declared_value` (COD must not exceed it).
     *
     * @param  array<string, mixed>  $addons
     * @return array<string, mixed>
     */
    private function applyCashOnDeliveryAddons(Order $order, ?Batch $batch, array $addons): array
    {
        $cod = round($this->resolveCashOnDelivery($order), 2);
        if ($cod <= 0) {
            return $addons;
        }

        $maxWithoutDeclared = (float)config('belpost.defaults.max_cod_without_declared_value', 238);
        $hasDeclared = $this->batchHasEffectiveDeclaredValue($batch);

        if ($cod > $maxWithoutDeclared && !$hasDeclared) {
            throw new BelpostApiException(
                "Order #{$order->id}: наложенный платёж {$cod} BYN превышает лимит {$maxWithoutDeclared} BYN "
                . 'без объявленной ценности. Включите «С объявленной ценностью» в параметрах партии.',
            );
        }

        if ($hasDeclared) {
            $addons['declared_value'] = max($this->resolveDeclaredValueAmount($order), $cod);
        }

        $addons['cash_on_delivery'] = isset($addons['declared_value'])
            ? min($cod, (float)$addons['declared_value'])
            : $cod;

        return $addons;
    }

    /**
     * When list flags include declared value or partial receipt, business API expects matching item addons.
     *
     * @param  array<string, mixed>  $addons
     * @return array<string, mixed>
     */
    private function applyBatchAddonsForDeclaredOrPartialReceipt(Order $order, ?Batch $batch, array $addons): array
    {
        if ($batch === null) {
            return $addons;
        }

        if ($this->batchHasEffectiveDeclaredValue($batch)) {
            $declared = $this->resolveDeclaredValueAmount($order);
            $cod = isset($addons['cash_on_delivery']) ? (float)$addons['cash_on_delivery'] : 0.0;
            $addons['declared_value'] = max($declared, $cod);
        }

        if ($this->batchHasEffectiveDeclaredValue($batch) || $this->partialReceiptRequiresShelfLifeAddon($batch)) {
            $days = (int)config('belpost.defaults.shelf_life_days', 10);
            if ($days < 1) {
                $days = 10;
            }
            $addons['shelf_life'] = $days;
        }

        return $addons;
    }

    private function batchHasEffectiveDeclaredValue(Batch $batch): bool
    {
        if (!$batch->is_declared_value) {
            return false;
        }

        return $this->resolveBatchPostalDeliveryEnum($batch)?->supportsDeclaredValueListFlag() ?? false;
    }

    /**
     * Adds shelf life addon when declaration or tariff-valid partial enclosure receipt applies.
     */
    private function partialReceiptRequiresShelfLifeAddon(Batch $batch): bool
    {
        if (!$batch->is_partial_receipt) {
            return false;
        }

        return $this->resolveBatchPostalDeliveryEnum($batch)?->supportsPartialReceiptOfEnclosures() ?? true;
    }

    private function resolveBatchPostalDeliveryEnum(?Batch $batch): ?BelpostPostalDeliveryType
    {
        if ($batch === null) {
            return null;
        }

        $type = $batch->postal_delivery_type;

        if ($type instanceof BelpostPostalDeliveryType) {
            return $type;
        }

        if (is_string($type) && $type !== '') {
            return BelpostPostalDeliveryType::tryFrom($type);
        }

        return BelpostPostalDeliveryType::tryFrom((string)config('belpost.defaults.postal_delivery_type'));
    }

    private function resolveItemCategory(?Batch $batch): int
    {
        $enum = $this->resolveBatchPostalDeliveryEnum($batch);

        if ($enum?->isEcommercePostal()) {
            $category = (int)config('belpost.defaults.item_category_ecommerce', 1);

            return in_array($category, [0, 1, 2], true) ? $category : 1;
        }

        $category = (int)config('belpost.defaults.item_category', 0);

        return in_array($category, [0, 1, 2], true) ? $category : 0;
    }

    private function resolveDeclaredValueAmount(Order $order): float
    {
        $raw = round((float)($order->total_price > 0 ? $order->total_price : $order->getItemsPrice()), 2);

        return max($raw, 0.01);
    }

    private function resolveS10Code(Order $order): ?string
    {
        $allowedSeries = config('belpost.defaults.s10_series_prefixes', ['PC']);
        if (!is_array($allowedSeries)) {
            $allowedSeries = ['PC'];
        }

        $serialMin = config('belpost.defaults.s10_serial_min');
        $serialMax = config('belpost.defaults.s10_serial_max');
        $serialMin = is_int($serialMin) || (is_string($serialMin) && $serialMin !== '') ? (int)$serialMin : null;
        $serialMax = is_int($serialMax) || (is_string($serialMax) && $serialMax !== '') ? (int)$serialMax : null;

        $sources = array_filter([
            $order->belpost_s10code,
            $this->resolveBelpostTrackNumber($order),
        ], static fn (mixed $value): bool => is_string($value) && trim($value) !== '');

        foreach ($sources as $raw) {
            $valid = $this->s10CodeValidator->validateForApi($raw, $allowedSeries);
            if ($valid === null) {
                continue;
            }

            $inRange = $this->s10CodeValidator->validateSerialRange($valid, $serialMin, $serialMax);
            if ($inRange !== null) {
                return $inRange;
            }
        }

        $normalized = $this->s10CodeValidator->normalize($order->belpost_s10code)
            ?? $this->s10CodeValidator->normalize($this->resolveBelpostTrackNumber($order));

        if ($normalized === null) {
            return null;
        }

        if (!$this->s10CodeValidator->isValid($normalized)) {
            $allowed = $allowedSeries !== [] ? implode(', ', $allowedSeries) : 'любая';

            throw new BelpostApiException(
                "Order #{$order->id}: неверный S10-код «{$normalized}». "
                . "Формат: XX12345678XBY (серии {$allowed}).",
            );
        }

        if ($allowedSeries !== [] && !$this->s10CodeValidator->isAllowedSeries($normalized, $allowedSeries)) {
            $prefix = $this->s10CodeValidator->seriesPrefix($normalized);
            $allowed = implode(', ', $allowedSeries);

            if (config('belpost.defaults.omit_s10code_on_series_mismatch', true)) {
                return null;
            }

            throw new BelpostApiException(
                "Order #{$order->id}: серия {$prefix} не в списке разрешённых ({$allowed}).",
            );
        }

        $serial = $this->s10CodeValidator->extractSerialNumber($normalized);
        if ($serial !== null && ($serialMin !== null || $serialMax !== null)) {
            if (($serialMin !== null && $serial < $serialMin) || ($serialMax !== null && $serial > $serialMax)) {
                $range = ($serialMin ?? '…') . '–' . ($serialMax ?? '…');

                throw new BelpostApiException(
                    "Order #{$order->id}: номер «{$normalized}» (серийный {$serial}) вне договорного диапазона Белпочты ({$range}). "
                    . 'Уточните границы в кабинете Белпочты и задайте BELPOST_S10_SERIAL_MIN / BELPOST_S10_SERIAL_MAX в .env.',
                );
            }
        }

        // Format OK; API may still reject if the number is not registered or already used at Belpost.
        return $normalized;
    }

    private function resolveBelpostTrackNumber(Order $order): ?string
    {
        $track = $order->track;
        if ($track !== null && filled($track->track_number)) {
            return $track->track_number;
        }

        $number = OrderTrack::query()
            ->where('order_id', $order->id)
            ->where('delivery_type_enum', DeliveryTypeEnum::BELPOST)
            ->value('track_number');

        return is_string($number) && trim($number) !== '' ? $number : null;
    }

    private function resolveRecipientEmail(Order $order): string
    {
        $email = trim((string)($order->email ?: $order->user?->email ?: ''));
        if ($email !== '') {
            return mb_strtolower($email);
        }

        foreach ([
            config('belpost.defaults.fallback_recipient_email'),
            config('app.email'),
            config('mail.from.address'),
        ] as $candidate) {
            $candidate = trim((string)($candidate ?? ''));
            if ($candidate !== '') {
                return mb_strtolower($candidate);
            }
        }

        return '';
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
