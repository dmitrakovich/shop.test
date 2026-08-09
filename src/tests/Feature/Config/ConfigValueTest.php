<?php

namespace Tests\Feature\Config;

use App\Enums\Config\ConfigKey;
use App\Models\Config;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ConfigValueTest extends TestCase
{
    use RefreshDatabase;

    public function test_value_reads_nested_config_path(): void
    {
        $this->assertSame('100.00', Config::value(ConfigKey::Installment, 'min_price'));
        $this->assertSame('200.00', Config::value(ConfigKey::Installment, 'min_price_3_parts'));
    }

    public function test_value_returns_default_when_path_is_missing(): void
    {
        Cache::forget('config.installment');

        $this->assertSame(150, Config::value(ConfigKey::Installment, 'missing_key', 150));
        $this->assertNull(Config::value(ConfigKey::Installment, 'missing_key'));
    }
}
