<?php

namespace Tests\Unit\Models;

use App\Models\Orders\OfflineOrder;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class OfflineOrderPhoneTest extends TestCase
{
    /**
     * @return array<string, array{string, string|null}>
     */
    public static function phones(): array
    {
        return [
            'digits' => ['375333715703', '+375333715703'],
            'formatted foreign' => ['+7962-726-17-59', '+79627261759'],
            'with spaces' => ['375 33 6066360', '+375336066360'],
            'none from 1C' => ['NONE', null],
            'empty' => ['', null],
            'garbage' => ['с', null],
            'text' => ['нет телефона', null],
        ];
    }

    #[DataProvider('phones')]
    public function test_phone_is_parsed_or_treated_as_missing(string $rawPhone, ?string $expected): void
    {
        $offlineOrder = new OfflineOrder(['user_phone' => $rawPhone]);

        $this->assertSame($expected, $offlineOrder->getPhone()?->toE164());
    }
}
