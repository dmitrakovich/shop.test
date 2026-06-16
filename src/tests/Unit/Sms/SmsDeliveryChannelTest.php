<?php

namespace Tests\Unit\Sms;

use App\Enums\Sms\SmsDeliveryChannel;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SmsDeliveryChannelTest extends TestCase
{
    #[DataProvider('resolveProvider')]
    public function test_resolve_maps_known_api_channel_values(?string $value, ?SmsDeliveryChannel $expected): void
    {
        $this->assertSame($expected, SmsDeliveryChannel::resolve($value));
    }

    /**
     * @return array<string, array{0: ?string, 1: ?SmsDeliveryChannel}>
     */
    public static function resolveProvider(): array
    {
        return [
            'viber' => ['viber', SmsDeliveryChannel::Viber],
            'sms' => ['sms', SmsDeliveryChannel::Sms],
            'smsbatch' => ['smsbatch', SmsDeliveryChannel::Sms],
            'uppercase sms' => ['SMS', SmsDeliveryChannel::Sms],
            'empty' => ['', null],
            'unknown' => ['push', null],
            'smsc' => ['smsc', null],
        ];
    }
}
