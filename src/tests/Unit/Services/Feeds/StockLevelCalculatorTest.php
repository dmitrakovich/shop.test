<?php

namespace Tests\Unit\Services\Feeds;

use App\Enums\Product\StockLevel;
use App\Services\Feeds\StockLevelCalculator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class StockLevelCalculatorTest extends TestCase
{
    #[DataProvider('resolveProvider')]
    public function test_resolve(
        int $sizesCount,
        int $shoePairs,
        int $nonePairs,
        StockLevel $expected,
    ): void {
        $this->assertSame(
            $expected,
            StockLevelCalculator::resolve($sizesCount, $shoePairs, $nonePairs),
        );
    }

    /**
     * @return array<string, array{int, int, int, StockLevel}>
     */
    public static function resolveProvider(): array
    {
        return [
            'shoe full by sizes alone' => [5, 1, 0, StockLevel::Full],
            'shoe full by sizes and pairs' => [4, 8, 0, StockLevel::Full],
            'shoe mid by sizes' => [3, 1, 0, StockLevel::Mid],
            'shoe mid by pairs' => [1, 5, 0, StockLevel::Mid],
            'shoe four sizes few pairs stays mid' => [4, 7, 0, StockLevel::Mid],
            'shoe size_none qty does not inflate pairs to full' => [4, 7, 20, StockLevel::Mid],
            'shoe low' => [2, 4, 0, StockLevel::Low],
            'one-size full' => [0, 0, 8, StockLevel::Full],
            'one-size mid' => [0, 0, 5, StockLevel::Mid],
            'one-size low' => [0, 0, 4, StockLevel::Low],
            'empty' => [0, 0, 0, StockLevel::Low],
        ];
    }
}
