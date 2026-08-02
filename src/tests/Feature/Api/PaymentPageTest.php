<?php

namespace Tests\Feature\Api;

use App\Enums\Order\OrderStatus;
use App\Enums\Payment\OnlinePaymentMethodEnum;
use App\Enums\Payment\OnlinePaymentStatusEnum;
use App\Facades\Device;
use App\Models\Orders\Order;
use App\Models\Payments\OnlinePayment;
use App\ValueObjects\Phone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionProperty;
use Tests\TestCase;

class PaymentPageTest extends TestCase
{
    use RefreshDatabase;

    private const string DEVICE_ID = '8d854825-6753-4a16-9056-9f36b7ac7b90';

    protected function setUp(): void
    {
        parent::setUp();

        (new ReflectionProperty(Device::class, 'currentDevice'))->setValue(null);

        config([
            'app.front_url' => 'http://front.test',
            'hgrosh.serviceproviderid' => '18466',
            'filesystems.disks.public.url' => 'http://media.test/media',
        ]);
    }

    public function test_erip_page_returns_payment_data(): void
    {
        $order = $this->createOrder();
        $payment = $this->createOnlinePayment([
            'order_id' => $order->id,
            'method_enum_id' => OnlinePaymentMethodEnum::ERIP,
            'payment_num' => $order->id . '-1',
            'payment_url' => $order->id . '-1',
            'amount' => 19.0,
            'currency_code' => 'BYN',
            'qr_code' => 'hgrosh/08-2026/abc.jpg',
        ]);

        $this->getJson('/api/v1/pay/erip/' . $payment->payment_url, $this->deviceHeaders())
            ->assertOk()
            ->assertJsonPath('payment_num', $payment->payment_num)
            ->assertJsonPath('order_id', $order->id)
            ->assertJsonPath('order_date', $order->created_at->format('d.m.Y'))
            ->assertJsonPath('amount', 19)
            ->assertJsonPath('currency_code', 'BYN')
            ->assertJsonPath('qr_code_url', 'http://media.test/media/hgrosh/08-2026/abc.jpg')
            ->assertJsonPath('epos_account', '18466-1-' . $payment->payment_num)
            ->assertJsonMissingPath('phone')
            ->assertJsonMissingPath('request_data');
    }

    public function test_erip_page_returns_404_for_unknown_payment(): void
    {
        $this->getJson('/api/v1/pay/erip/missing-1', $this->deviceHeaders())
            ->assertNotFound();
    }

    public function test_yandex_page_returns_payment_data(): void
    {
        $order = $this->createOrder();
        $payment = $this->createOnlinePayment([
            'order_id' => $order->id,
            'method_enum_id' => OnlinePaymentMethodEnum::YANDEX,
            'payment_num' => $order->id . '-1',
            'link_code' => 'yandex-link-code',
            'payment_url' => 'https://yookassa.test/pay',
            'link_expires_at' => now()->addHour(),
            'amount' => 1500.0,
            'currency_code' => 'RUB',
        ]);

        $this->getJson('/api/v1/pay/yandex/' . $payment->link_code, $this->deviceHeaders())
            ->assertOk()
            ->assertJsonPath('payment_num', $payment->payment_num)
            ->assertJsonPath('order_id', $order->id)
            ->assertJsonPath('amount', 1500)
            ->assertJsonPath('currency_code', 'RUB')
            ->assertJsonPath('link_code', 'yandex-link-code')
            ->assertJsonPath('epos_account', null)
            ->assertJsonPath('qr_code_url', null);
    }

    public function test_yandex_page_returns_404_for_erip_link_code(): void
    {
        $order = $this->createOrder();
        $this->createOnlinePayment([
            'order_id' => $order->id,
            'method_enum_id' => OnlinePaymentMethodEnum::ERIP,
            'payment_num' => $order->id . '-1',
            'payment_url' => $order->id . '-1',
            'link_code' => 'erip-code',
            'amount' => 10.0,
            'currency_code' => 'BYN',
        ]);

        $this->getJson('/api/v1/pay/yandex/erip-code', $this->deviceHeaders())
            ->assertNotFound();
    }

    public function test_yandex_page_returns_404_for_expired_link(): void
    {
        $order = $this->createOrder();
        $this->createOnlinePayment([
            'order_id' => $order->id,
            'method_enum_id' => OnlinePaymentMethodEnum::YANDEX,
            'payment_num' => $order->id . '-1',
            'link_code' => 'expired-yandex',
            'payment_url' => 'https://yookassa.test/pay',
            'link_expires_at' => now()->subMinute(),
            'amount' => 100.0,
            'currency_code' => 'RUB',
        ]);

        $this->getJson('/api/v1/pay/yandex/expired-yandex', $this->deviceHeaders())
            ->assertNotFound();
    }

    public function test_resolve_link_code_returns_payment_url(): void
    {
        $order = $this->createOrder();
        $this->createOnlinePayment([
            'order_id' => $order->id,
            'method_enum_id' => OnlinePaymentMethodEnum::YANDEX,
            'payment_num' => $order->id . '-1',
            'link_code' => 'resolve-code',
            'payment_url' => 'https://yookassa.test/confirm',
            'link_expires_at' => now()->addMinutes(59),
            'amount' => 100.0,
            'currency_code' => 'RUB',
        ]);

        $this->postJson('/api/v1/pay/link-code/resolve-code/resolve', [], $this->deviceHeaders())
            ->assertOk()
            ->assertExactJson([
                'payment_url' => 'https://yookassa.test/confirm',
            ]);
    }

    public function test_resolve_link_code_returns_404_for_non_yandex_method(): void
    {
        $order = $this->createOrder();
        $this->createOnlinePayment([
            'order_id' => $order->id,
            'method_enum_id' => OnlinePaymentMethodEnum::ERIP,
            'payment_num' => $order->id . '-1',
            'payment_url' => $order->id . '-1',
            'link_code' => 'erip-resolve',
            'amount' => 10.0,
            'currency_code' => 'BYN',
        ]);

        $this->postJson('/api/v1/pay/link-code/erip-resolve/resolve', [], $this->deviceHeaders())
            ->assertNotFound();
    }

    public function test_resolve_link_code_returns_404_when_payment_url_empty(): void
    {
        $order = $this->createOrder();
        $this->createOnlinePayment([
            'order_id' => $order->id,
            'method_enum_id' => OnlinePaymentMethodEnum::YANDEX,
            'payment_num' => $order->id . '-1',
            'link_code' => 'empty-url',
            'payment_url' => null,
            'link_expires_at' => now()->addHour(),
            'amount' => 100.0,
            'currency_code' => 'RUB',
        ]);

        $this->postJson('/api/v1/pay/link-code/empty-url/resolve', [], $this->deviceHeaders())
            ->assertNotFound();
    }

    public function test_resolve_link_code_returns_404_for_expired_link(): void
    {
        $order = $this->createOrder();
        $this->createOnlinePayment([
            'order_id' => $order->id,
            'method_enum_id' => OnlinePaymentMethodEnum::YANDEX,
            'payment_num' => $order->id . '-1',
            'link_code' => 'expired-resolve',
            'payment_url' => 'https://yookassa.test/confirm',
            'link_expires_at' => now()->subMinute(),
            'amount' => 100.0,
            'currency_code' => 'RUB',
        ]);

        $this->postJson('/api/v1/pay/link-code/expired-resolve/resolve', [], $this->deviceHeaders())
            ->assertNotFound();
    }

    public function test_api_pay_routes_are_registered(): void
    {
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('api.pay.erip'));
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('api.pay.yandex'));
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('api.pay.link-code.resolve'));
    }

    public function test_legacy_web_pay_routes_remain_registered(): void
    {
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('pay.erip'));
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('pay.yandex'));
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('pay.link-code'));
    }

    public function test_erip_payment_link_points_to_frontend(): void
    {
        $order = $this->createOrder();
        $payment = $this->createOnlinePayment([
            'order_id' => $order->id,
            'method_enum_id' => OnlinePaymentMethodEnum::ERIP,
            'payment_num' => $order->id . '-1',
            'payment_url' => $order->id . '-1',
            'amount' => 19.0,
            'currency_code' => 'BYN',
        ]);

        $this->assertSame('http://front.test/pay/erip/' . $payment->payment_url, $payment->link);
    }

    public function test_yandex_payment_link_points_to_frontend(): void
    {
        $order = $this->createOrder();
        $payment = $this->createOnlinePayment([
            'order_id' => $order->id,
            'method_enum_id' => OnlinePaymentMethodEnum::YANDEX,
            'payment_num' => $order->id . '-1',
            'link_code' => 'yandex-front-link',
            'payment_url' => 'https://yookassa.test/pay',
            'amount' => 1500.0,
            'currency_code' => 'RUB',
        ]);

        $this->assertSame('http://front.test/pay/yandex/yandex-front-link', $payment->link);
    }

    /**
     * @return array<string, string>
     */
    private function deviceHeaders(): array
    {
        return ['device-id' => self::DEVICE_ID];
    }

    private function createOrder(): Order
    {
        return Order::query()->create([
            'first_name' => 'Тест',
            'phone' => Phone::fromRawString('+375291112233'),
            'total_price' => 100,
            'currency' => 'BYN',
            'rate' => 1,
            'status' => OrderStatus::NEW,
            'status_updated_at' => now(),
            'created_at' => now()->setDate(2026, 7, 28)->setTime(12, 0),
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createOnlinePayment(array $attributes): OnlinePayment
    {
        return OnlinePayment::withoutEvents(fn (): OnlinePayment => OnlinePayment::query()->create(array_merge([
            'currency_value' => 1,
            'last_status_enum_id' => OnlinePaymentStatusEnum::PENDING,
        ], $attributes)));
    }
}
