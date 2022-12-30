<?php

namespace App\Models\User;

class CachedUser
{
    public function __construct(
        public bool $hasReviewAfterOrder = false
    ) {
    }
}
