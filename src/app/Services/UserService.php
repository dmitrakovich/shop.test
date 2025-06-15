<?php

namespace App\Services;

use App\Data\Order\OrderData;
use App\Events\Analytics\Registered;
use App\Models\User\User;
use App\ValueObjects\Phone;

class UserService
{
    /**
     * AuthService constructor.
     */
    public function __construct(private User $user) {}

    public function findOrCreateByPhone(Phone $phone): User
    {
        return $this->user->getByPhone($phone) ?? $this->user->query()->create([
            'phone' => $phone->forSave(),
        ]);
    }

    /**
     * @throws \libphonenumber\NumberParseException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFailByPhone(string $phone): User
    {
        return $this->user->getByPhone(Phone::fromRawString($phone));
    }

    /**
     * Find user or create new by phone number
     */
    public function getOrCreateByOrderData(OrderData $orderData): User
    {
        $phone = Phone::fromRawString($orderData->phone);

        $user = $this->user->getByPhone($phone) ?? $this->user->query()->create([
            'phone' => $phone->forSave(),
            'first_name' => $orderData->firstName,
            'last_name' => $orderData->lastName,
            'patronymic_name' => $orderData->patronymicName,
            'email' => $orderData->email,
        ]);

        if ($orderData->userAddr) {
            /** @var \App\Models\User\Address $address */
            $address = $user->lastAddress()->firstOrNew();
            $address->fill([
                'country_id' => $orderData->country?->id,
                'city' => $orderData->city,
                'address' => $orderData->userAddr,
                'approve' => $address->approve && $orderData->userAddr === $address->address,
            ]);
            $address->save();
        }

        if ($user->wasRecentlyCreated) {
            event(new Registered($user));
        }

        return $user;
    }
}
