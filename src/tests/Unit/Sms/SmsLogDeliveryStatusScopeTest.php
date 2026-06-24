<?php

namespace Tests\Unit\Sms;

use App\Enums\Sms\SmsDeliveryChannel;
use App\Enums\Sms\SmsDeliveryStatus;
use App\Models\Logs\SmsLog;
use Tests\TestCase;

class SmsLogDeliveryStatusScopeTest extends TestCase
{
    public function test_pending_delivery_status_update_includes_viber_delivered_messages(): void
    {
        $bindings = SmsLog::query()
            ->pendingDeliveryStatusUpdate()
            ->getBindings();

        $this->assertContains(SmsDeliveryChannel::Viber->value, $bindings);
        $this->assertContains(SmsDeliveryStatus::Delivered->value, $bindings);
        $this->assertContains(SmsDeliveryStatus::ClassicDelivered->value, $bindings);
    }
}
