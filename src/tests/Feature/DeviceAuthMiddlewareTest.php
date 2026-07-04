<?php

namespace Tests\Feature;

use Tests\TestCase;

class DeviceAuthMiddlewareTest extends TestCase
{
    public function test_app_init_requires_device_id_header(): void
    {
        $this->getJson('/api/v1/app-init')
            ->assertUnauthorized();
    }

    public function test_app_init_rejects_sql_injection_in_device_id(): void
    {
        $this->getJson('/api/v1/app-init', [
            'device-id' => "8d854825-6753-4a16-9056-9f36b7ac7b90%' AND 2*3*8=6*8 AND 'eSut'!='eSut%",
        ])->assertUnauthorized();
    }

    public function test_app_init_rejects_non_uuid_device_id(): void
    {
        $this->getJson('/api/v1/app-init', [
            'device-id' => 'not-a-uuid',
        ])->assertUnauthorized();
    }

    public function test_app_init_rejects_plain_text_device_id(): void
    {
        $this->getJson('/api/v1/app-init', [
            'device-id' => '8d854825-6753-4a16-9056-9f36b7ac7b90-extra',
        ])->assertUnauthorized();
    }
}
