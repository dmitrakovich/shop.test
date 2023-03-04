<?php

namespace App\Models\Data;

use App\Models\User\User;
use Deliveries\DeliveryMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Payments\PaymentMethod;

class OrderData
{
    /**
     * The user who make the order
     */
    public readonly ?User $user;

    /**
     * Order payment method
     */
    public readonly ?PaymentMethod $paymentMethod;

    /**
     * Order delivery method
     */
    public readonly ?DeliveryMethod $deliveryMethod;

    /**
     * Order creation date
     */
    public readonly ?Carbon $created_at;

    /**
     * OrderData constructor
     */
    public function __construct(
        public ?int $user_id,
        public string $first_name,
        public ?string $patronymic_name,
        public ?string $last_name,
        public string $order_method,
        public ?string $email,
        public string $phone,
        public ?string $comment,
        public string $currency,
        public float $rate,
        public ?int $payment_id,
        public ?int $delivery_id,
        public ?int $country_id,
        public ?string $city,
        public ?string $user_addr,
        public ?string $utm_medium,
        public ?string $utm_source,
        public ?string $utm_campaign,
        public ?string $utm_content,
        public ?string $utm_term,
        public float $total_price = 0,
        public ?string $status_key = null,
        ?string $created_at = null,
        ...$otherData
    ) {
        $this->user = $this->findModelOrFail(new User(), $this->user_id);
        $this->paymentMethod = $this->findModelOrFail(new PaymentMethod(), $this->payment_id);
        $this->deliveryMethod = $this->findModelOrFail(new DeliveryMethod(), $this->delivery_id);
        $this->created_at = $this->createDate($created_at);
    }

    /**
     * Find order property model
     * TODO: create a separate trait
     */
    private function findModelOrFail(Model $model, ?int $id): ?Model
    {
        return $id ? $model->query()->findOrFail($id) : null;
    }

    /**
     * Create a new Carbon instance.
     * TODO: create a separate trait
     */
    public function createDate(\DateTimeInterface|string|null $date): ?Carbon
    {
        return $date ? new Carbon($date) : null;
    }

    /**
     * Prepare order data to save
     */
    public function prepareToSave(): array
    {
        return array_filter((array)$this);
    }
}
