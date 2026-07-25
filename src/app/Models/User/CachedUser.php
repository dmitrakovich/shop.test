<?php

namespace App\Models\User;

use App\Enums\Feedback\ReviewDiscountType;

class CachedUser
{
    public function __construct(
        public ?ReviewDiscountType $reviewDiscountType = null,
    ) {}
}
