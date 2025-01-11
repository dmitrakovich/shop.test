<?php

namespace App\Events\User;

use App\Models\User\User;
use Illuminate\Queue\SerializesModels;

class UserLogin
{
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public string $guard,
        public User $user,
        public bool $remember
    ) {}
}
