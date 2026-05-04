<?php

namespace Tests\Feature;

use App\Enums\Consent\ConsentFormEnum;
use App\Facades\Device as DeviceFacade;
use App\Http\Middleware\PersistDeviceConsentHeaders;
use App\Models\User\Device;
use App\Models\User\DeviceConsent;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ReflectionProperty;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class PersistDeviceConsentHeadersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $this->createSchema();
        $this->setCurrentDevice();
    }

    public function test_it_persists_consent_headers_with_request_identity_data(): void
    {
        $request = $this->requestWithConsentHeaders([
            'X-Cookie-Analytics-Enabled' => 'true',
            'X-Cookie-Marketing-Enabled' => 'false',
            'X-Personal-Data-Consent' => 'true',
            'X-Personal-Data-Consent-Recorded-At' => '2026-05-03 20:16:00',
        ], [
            'user_name' => '  Jane Doe  ',
            'phone' => '  +375291234567  ',
        ]);

        $response = $this->handle($request, ConsentFormEnum::Order);

        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        $consent = DeviceConsent::query()->sole();

        $this->assertSame(1, $consent->device_id);
        $this->assertSame('Jane Doe', $consent->fio);
        $this->assertSame('+375291234567', $consent->phone);
        $this->assertTrue($consent->cookie_analytics_enabled);
        $this->assertFalse($consent->cookie_marketing_enabled);
        $this->assertTrue($consent->personal_data_consent);
        $this->assertSame('2026-05-03 20:16:00', $consent->personal_data_consent_recorded_at?->format('Y-m-d H:i:s'));
        $this->assertSame(ConsentFormEnum::Order, $consent->consent_request_source);
    }

    public function test_it_does_not_duplicate_rows_when_consent_values_do_not_change(): void
    {
        $request = $this->requestWithConsentHeaders([
            'X-Cookie-Analytics-Enabled' => 'true',
            'X-Cookie-Marketing-Enabled' => 'false',
            'X-Personal-Data-Consent' => 'true',
        ], [
            'user_name' => 'Jane Doe',
            'phone' => '+375291234567',
        ]);

        $this->handle($request, ConsentFormEnum::Order);
        $this->handle($request, ConsentFormEnum::Order);

        $this->assertSame(1, DeviceConsent::query()->count());
    }

    public function test_it_records_explicit_false_when_a_consent_value_changes(): void
    {
        $this->handle($this->requestWithConsentHeaders([
            'X-Personal-Data-Consent' => 'true',
        ]), ConsentFormEnum::Feedback);

        $this->handle($this->requestWithConsentHeaders([
            'X-Personal-Data-Consent' => 'false',
        ]), ConsentFormEnum::Feedback);

        $this->assertSame(2, DeviceConsent::query()->count());
        $this->assertFalse(DeviceConsent::query()->latest('id')->firstOrFail()->personal_data_consent);
    }

    /**
     * @param  array<string, string>  $headers
     * @param  array<string, string>  $payload
     */
    private function requestWithConsentHeaders(array $headers, array $payload = []): Request
    {
        $request = Request::create('/test-consent', 'POST', $payload);

        foreach ($headers as $name => $value) {
            $request->headers->set($name, $value);
        }

        return $request;
    }

    private function handle(Request $request, ConsentFormEnum $form): Response
    {
        return (new PersistDeviceConsentHeaders())->handle(
            $request,
            fn (): Response => new Response('', Response::HTTP_NO_CONTENT),
            (string)$form->value,
        );
    }

    private function createSchema(): void
    {
        Schema::dropAllTables();

        Schema::create('devices', function (Blueprint $table): void {
            $table->id();
            $table->timestamps();
        });

        Schema::create('device_consents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('device_id')->nullable();
            $table->foreignId('user_id')->nullable();
            $table->string('fio')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('cookie_analytics_enabled')->nullable();
            $table->boolean('cookie_marketing_enabled')->nullable();
            $table->timestamp('personal_data_consent_recorded_at')->nullable();
            $table->boolean('personal_data_consent')->nullable();
            $table->unsignedTinyInteger('consent_request_source')->nullable();
            $table->timestamps();
        });
    }

    private function setCurrentDevice(): void
    {
        DB::table('devices')->insert(['id' => 1]);

        $property = new ReflectionProperty(DeviceFacade::class, 'currentDevice');
        $property->setValue(Device::query()->findOrFail(1));
    }
}
