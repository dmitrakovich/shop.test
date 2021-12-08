<?php

namespace App\Models\Enum;

class OrderStatus implements Enum
{
    use EnumTrait;

    const CREATED = 'new';
    const CANCELED = 'canceled';
    const IN_WORK = 'in_work';
    const WAITING_PAYMENT = 'wait_payment';
    const PAID = 'paid';
    const SENT = 'sent';
    const FITTING = 'fitting';
    const COMPLETE = 'complete';
    const RETURN = 'return';
}
