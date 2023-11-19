<?php

namespace App\Events\Analytics;

use App\Models\User\User;

class Registered extends AbstractAnalyticEvent
{
    /**
     * Create a new event instance.
     */
    public function __construct(public User $user)
    {
        $this->setAnalyticData();
    }
}
