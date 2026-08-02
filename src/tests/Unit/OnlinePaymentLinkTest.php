<?php

namespace Tests\Unit;

use App\Enums\Payment\OnlinePaymentMethodEnum;
use App\Models\Payments\OnlinePayment;
use Tests\TestCase;

class OnlinePaymentLinkTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['app.front_url' => 'http://front.test']);
    }

    public function test_erip_link_uses_front_url(): void
    {
        $payment = new OnlinePayment([
            'method_enum_id' => OnlinePaymentMethodEnum::ERIP,
            'payment_url' => '47492-1',
        ]);

        $this->assertSame('http://front.test/pay/erip/47492-1', $payment->link);
    }

    public function test_yandex_link_uses_front_url(): void
    {
        $payment = new OnlinePayment([
            'method_enum_id' => OnlinePaymentMethodEnum::YANDEX,
            'link_code' => 'abc123',
        ]);

        $this->assertSame('http://front.test/pay/yandex/abc123', $payment->link);
    }

    public function test_link_is_null_without_payment_identifiers(): void
    {
        $erip = new OnlinePayment([
            'method_enum_id' => OnlinePaymentMethodEnum::ERIP,
        ]);
        $yandex = new OnlinePayment([
            'method_enum_id' => OnlinePaymentMethodEnum::YANDEX,
        ]);

        $this->assertNull($erip->link);
        $this->assertNull($yandex->link);
    }
}
