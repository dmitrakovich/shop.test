<?php

namespace Tests\Unit\Sms;

use App\Enums\Sms\SmsDeliveryStatus;
use Tests\TestCase;

class SmsDeliveryStatusTest extends TestCase
{
    public function test_delivered_values_include_api_delivered_statuses(): void
    {
        $this->assertSame(
            [
                SmsDeliveryStatus::Delivered->value,
                SmsDeliveryStatus::ClassicDelivered->value,
            ],
            SmsDeliveryStatus::deliveredValues(),
        );
    }
}
