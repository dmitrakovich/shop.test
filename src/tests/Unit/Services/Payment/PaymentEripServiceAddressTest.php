<?php

namespace Tests\Unit\Services\Payment;

use App\Models\Orders\Order;
use App\Services\Payment\Methods\PaymentEripService;
use ReflectionMethod;
use Tests\TestCase;

class PaymentEripServiceAddressTest extends TestCase
{
    public function test_strips_quotes_from_shop_pvz_address(): void
    {
        $order = new Order([
            'stock_id' => 32,
            'user_addr' => 'Минск, ТЦ "Dana Mall"',
        ]);

        $this->assertSame('Минск, ТЦ Dana Mall', $this->normalizeAddress($order));
    }

    public function test_strips_unicode_quotes_from_shop_pvz_address(): void
    {
        $order = new Order([
            'stock_id' => 17,
            'user_addr' => 'г. Минск, ул. Притыцкого, 156, ТЦ “Green City”, 2 этаж',
        ]);

        $this->assertSame(
            'г. Минск, ул. Притыцкого, 156, ТЦ Green City, 2 этаж',
            $this->normalizeAddress($order)
        );
    }

    public function test_strips_brackets_and_control_chars_from_shop_pvz_address(): void
    {
        $order = new Order([
            'stock_id' => 32,
            'user_addr' => "Минск,\nТЦ <Dana> Mall\t`центр`",
        ]);

        $this->assertSame('Минск, ТЦ Dana Mall центр', $this->normalizeAddress($order));
    }

    public function test_keeps_address_without_stock_unchanged(): void
    {
        $order = new Order([
            'stock_id' => null,
            'user_addr' => 'Минск, ТЦ "Dana Mall"',
        ]);

        $this->assertSame('Минск, ТЦ "Dana Mall"', $this->normalizeAddress($order));
    }

    private function normalizeAddress(Order $order): ?string
    {
        $method = new ReflectionMethod(PaymentEripService::class, 'normalizeEposAddress');

        return $method->invoke(new PaymentEripService(), $order);
    }
}
