<?php

namespace App\Models\Enum;

class OrderItemStatus implements Enum
{
    use EnumTrait;

    const CREATED = 'new';
    const DELETE = 'delete';
    const CONFIRMED = 'confirmed';
    const PACKAGE = 'package';
    const SENT = 'sent';
    const FITTING = 'fitting';
    const COMPLETE = 'complete';
    const RETURN = 'return';
}
