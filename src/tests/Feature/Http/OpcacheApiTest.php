<?php

namespace Tests\Feature\Http;

use App\Http\Middleware\DeviceDetect;
use Tests\TestCase;

class OpcacheApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['app.front_url' => 'http://front.test']);

        // Unknown web paths fall through the storefront redirect, which creates
        // a device. That lookup is unrelated to the removed OPcache routes.
        $this->withoutMiddleware(DeviceDetect::class);
    }

    public function test_spoofed_localhost_cannot_use_opcache_http_api(): void
    {
        $headers = [
            'CF-Connecting-IP' => '127.0.0.1',
            'X-Forwarded-For' => '127.0.0.1',
        ];

        foreach (['status', 'clear', 'config', 'compile'] as $action) {
            $response = $this->withHeaders($headers)->get('/opcache-api/' . $action);

            $response->assertRedirect('http://front.test/opcache-api/' . $action);
            $this->assertFalse($response->isSuccessful());
            $this->assertStringNotContainsString('"result"', (string)$response->getContent());
        }
    }
}
