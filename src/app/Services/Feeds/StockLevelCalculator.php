<?php

namespace App\Services\Feeds;

use App\Enums\Product\StockLevel;
use App\Models\AvailableSizes;
use Illuminate\Support\Facades\DB;

/**
 * Classifies products by size-range completeness and total pair count.
 *
 * Thresholds (shoe sizes exclude size_none):
 * - full: ≥5 sizes, or ≥4 sizes and ≥8 pairs
 * - mid: ≥3 sizes, or ≥5 pairs
 * - low: everything else
 */
final class StockLevelCalculator
{
    /**
     * Resolve stock level from aggregated metrics.
     */
    public static function resolve(int $sizesCount, int $pairs): StockLevel
    {
        if ($sizesCount >= 5 || ($sizesCount >= 4 && $pairs >= 8)) {
            return StockLevel::Full;
        }

        if ($sizesCount >= 3 || $pairs >= 5) {
            return StockLevel::Mid;
        }

        return StockLevel::Low;
    }

    /**
     * Map product_id => StockLevel from available_sizes (summed across warehouses).
     *
     * @return array<int, StockLevel>
     */
    public static function levelsByProductId(): array
    {
        $sizeFields = AvailableSizes::getSizeFields();
        $shoeFields = array_values(array_filter(
            $sizeFields,
            static fn (string $field): bool => $field !== 'size_none',
        ));

        $sizesExpr = implode(' + ', array_map(
            static fn (string $field): string => 'IF(SUM(`' . $field . '`) > 0, 1, 0)',
            $shoeFields,
        ));
        $pairsExpr = implode(' + ', array_map(
            static fn (string $field): string => 'COALESCE(SUM(`' . $field . '`), 0)',
            $sizeFields,
        ));

        /** @var \Illuminate\Support\Collection<int, object{product_id: int|string, sizes_count: int|string|float, pairs: int|string|float}> $rows */
        $rows = DB::table((new AvailableSizes())->getTable())
            ->whereNotNull('product_id')
            ->groupBy('product_id')
            ->select([
                'product_id',
                DB::raw("($sizesExpr) as sizes_count"),
                DB::raw("($pairsExpr) as pairs"),
            ])
            ->get();

        $levels = [];
        foreach ($rows as $row) {
            $levels[(int)$row->product_id] = self::resolve(
                (int)$row->sizes_count,
                (int)$row->pairs,
            );
        }

        return $levels;
    }
}
