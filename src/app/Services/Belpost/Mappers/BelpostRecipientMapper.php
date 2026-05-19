<?php

namespace App\Services\Belpost\Mappers;

use App\Models\Orders\Order;
use App\Services\Belpost\Geo\BelpostRecipientAddressResolver;
use App\Services\Belpost\Support\BelpostPhoneNormalizer;

class BelpostRecipientMapper
{
    public function __construct(
        private readonly BelpostRecipientAddressResolver $addressResolver,
        private readonly BelpostPhoneNormalizer $phoneNormalizer,
    ) {}

    public function foreignIdFor(Order $order): string
    {
        return $order->user_id ? (string)$order->user_id : 'order-' . $order->id;
    }

    /**
     * @return array<string, mixed>
     */
    public function toPayload(Order $order, string $foreignId): array
    {
        $order->loadMissing('user.lastAddress');

        $email = mb_strtolower(trim((string)(
            $order->email
            ?: $order->user?->email
            ?: config('belpost.defaults.fallback_recipient_email', config('app.email'))
        )));

        $phone = $this->phoneNormalizer->normalize($order->phone)
            ?? ($order->user ? $this->phoneNormalizer->normalize($order->user->phone) : null);

        return array_filter([
            'foreign_id' => $foreignId,
            'type' => 'individual',
            'first_name' => mb_substr($order->first_name ?: 'Получатель', 0, 20),
            'second_name' => $order->patronymic_name ? mb_substr($order->patronymic_name, 0, 20) : null,
            'last_name' => mb_substr($order->last_name ?: $order->first_name ?: '—', 0, 25),
            'phone' => $phone,
            'email' => mb_substr($email, 0, 30),
            'address' => $this->addressResolver->resolve($order),
        ], fn ($value) => $value !== null && $value !== '');
    }
}
