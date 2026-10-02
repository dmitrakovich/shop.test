<?php

namespace Tests\Feature\Api;

use App\Enums\Device\DeviceType;
use App\Facades\Device;
use App\Models\User\Device as UserDevice;
use App\Models\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionProperty;
use Tests\TestCase;

class UserEndpointTest extends TestCase
{
    use RefreshDatabase;

    private const string DEVICE_ID = '8d854825-6753-4a16-9056-9f36b7ac7b90';

    private const string OTP_CODE = '654321';

    protected function setUp(): void
    {
        parent::setUp();

        (new ReflectionProperty(Device::class, 'currentDevice'))->setValue(null);
    }

    public function test_user_endpoint_does_not_expose_otp_or_device(): void
    {
        $user = $this->createUserWithOtp();

        $serialized = $user->fresh()->toArray();
        $this->assertArrayNotHasKey('otp_code', $serialized);
        $this->assertArrayNotHasKey('otp_expires_at', $serialized);

        $response = $this->withToken($user->createToken('api')->plainTextToken)
            ->getJson('/api/v1/user', [
                'device-id' => self::DEVICE_ID,
            ]);

        $response->assertNotFound();
        $this->assertStringNotContainsString(self::OTP_CODE, (string)$response->getContent());
        $this->assertStringNotContainsString('otp_code', (string)$response->getContent());
        $this->assertStringNotContainsString('otp_expires_at', (string)$response->getContent());
        $this->assertStringNotContainsString('ip_address', (string)$response->getContent());
        $response->assertJsonMissingPath('device');
        $response->assertJsonMissingPath('user');
    }

    public function test_account_profile_returns_user_resource_without_otp(): void
    {
        UserDevice::withoutEvents(fn () => UserDevice::query()->create([
            'api_id' => self::DEVICE_ID,
            'type' => DeviceType::DESKTOP,
            'agent' => 'phpunit',
        ]));

        $user = $this->createUserWithOtp();

        $this->withToken($user->createToken('api')->plainTextToken)
            ->getJson('/api/v1/account/profile', [
                'device-id' => self::DEVICE_ID,
            ])
            ->assertOk()
            ->assertJsonPath('id', $user->id)
            ->assertJsonPath('first_name', 'Ivan')
            ->assertJsonMissingPath('otp_code')
            ->assertJsonMissingPath('otp_expires_at')
            ->assertJsonMissingPath('device');
    }

    private function createUserWithOtp(): User
    {
        return User::query()->create([
            'group_id' => 1,
            'first_name' => 'Ivan',
            'phone' => 375291119988,
            'otp_code' => self::OTP_CODE,
            'otp_expires_at' => now()->addMinutes(10),
        ]);
    }
}
