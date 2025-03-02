<?php

namespace App\Services;

use App\Data\Order\OrderData;
use App\Events\Analytics\Registered;
use App\Models\User\User;

class UserService
{
    /**
     * AuthService constructor.
     */
    public function __construct(private User $user) {}

    /**
     * Find user or create new by phone number
     */
    public function getOrCreateByOrderData(OrderData $orderData): User
    {
        $user = $this->user->getByPhone($phone) ?? $this->user->query()->create([
            'phone' => $phone,
            ...$userData,
        ]);

        if (!empty($userAddress)) {
            $user->load('lastAddress');
            if ($user->lastAddress) {
                $user->lastAddress->fill($userAddress);
                if ($user->lastAddress->isDirty()) {
                    $user->lastAddress->approve = false;
                    $user->lastAddress->save();
                }
            } else {
                $user->addresses()->create($userAddress);
            }
        }

        if ($user->wasRecentlyCreated) {
            event(new Registered($user));
        }

        return $user;
    }
}
