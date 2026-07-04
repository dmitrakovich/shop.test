<?php

namespace Tests\Unit;

use App\Exceptions\ReportDeviceError;
use App\Facades\Device;
use App\Models\User\Device as UserDevice;
use Illuminate\Http\Request;
use LogicException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use ReflectionProperty;
use Tests\TestCase;

class ReportDeviceErrorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resetDeviceBinding();
    }

    private function resetDeviceBinding(): void
    {
        (new ReflectionProperty(Device::class, 'currentDevice'))->setValue(null);
    }

    public function test_current_throws_when_device_is_not_set(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Device has not been set.');

        Device::current();
    }

    public function test_it_does_not_throw_when_device_is_not_resolved(): void
    {
        app(ReportDeviceError::class)(new \Exception('test'));

        $this->assertFalse(Device::isResolved());
    }

    public function test_it_skips_console_process(): void
    {
        $this->app->instance('request', Request::create('/api/v1/app-init'));
        $this->app->instance('env', 'production');

        app(ReportDeviceError::class)(new \Exception('test'));

        $this->assertFalse(Device::isResolved());
    }

    public function test_it_skips_filament_admin_request(): void
    {
        $this->app->instance('request', Request::create('/admin/login'));

        app(ReportDeviceError::class)(new \Exception('test'));

        $this->assertFalse(Device::isResolved());
    }

    public function test_it_skips_livewire_request(): void
    {
        $this->app->instance('request', Request::create('/livewire-68b5a557/update', 'POST'));

        app(ReportDeviceError::class)(new \Exception('test'));

        $this->assertFalse(Device::isResolved());
    }

    public function test_it_skips_legacy_admin_request(): void
    {
        config(['admin.route.prefix' => 'old-admin']);
        $this->app->instance('request', Request::create('/old-admin/orders'));

        app(ReportDeviceError::class)(new \Exception('test'));

        $this->assertFalse(Device::isResolved());
    }

    public function test_it_skips_unpersisted_device(): void
    {
        $this->app->instance('request', Request::create('/api/v1/app-init'));
        Device::setConsoleDevice();

        app(ReportDeviceError::class)(new \Exception('test'));

        $this->assertTrue(Device::isResolved());
        $this->assertFalse(Device::current()->exists);
    }

    public function test_it_registers_error_for_persisted_device_on_api_request(): void
    {
        $this->app->instance('request', Request::create('/api/v1/app-init'));

        $exception = new \Exception('test failure', 42);

        $device = Mockery::mock(UserDevice::class)->makePartial();
        $device->exists = true;
        $device->shouldReceive('registerError')->once()->with($exception);

        Device::setDevice($device);

        app(ReportDeviceError::class)($exception);
    }
}
