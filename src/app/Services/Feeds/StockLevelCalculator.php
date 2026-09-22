<?php

namespace App\Services\Feeds;

use App\Enums\Product\StockLevel;
use App\Models\AvailableSizes;
use Illuminate\Support\Facades\DB;

/**
 * Classifies products by size-range completeness and sellable quantity.
 *
 * Shoe sizes (exclude size_none from both metrics):
 * - full: ≥5 sizes, or ≥4 sizes and ≥8 pairs
 * - mid: ≥3 sizes, or ≥5 pairs
 * - low: everything else
 *
 * One-size products (only size_none, no shoe sizes) use a quantity scale:
 * - full: ≥8 units
 * - mid: ≥5 units
 * - low: everything else
 */
final class StockLevelCalculator
{
    /**
     * Resolve stock level from aggregated metrics.
     *
     * @param  int  $sizesCount  Distinct shoe sizes with qty > 0 (excludes size_none)
     * @param  int  $shoePairs  Total qty across shoe size fields only
     * @param  int  $nonePairs  Total qty in size_none (bags / accessories)
     */
    public static function resolve(int $sizesCount, int $shoePairs, int $nonePairs = 0): StockLevel
    {
        if ($sizesCount === 0) {
            if ($nonePairs >= 8) {
                return StockLevel::Full;
            }

            if ($nonePairs >= 5) {
                return StockLevel::Mid;
            }

            return StockLevel::Low;
        }

        if ($sizesCount >= 5 || ($sizesCount >= 4 && $shoePairs >= 8)) {
            return StockLevel::Full;
        }

        if ($sizesCount >= 3 || $shoePairs >= 5) {
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
        $shoePairsExpr = implode(' + ', array_map(
            static fn (string $field): string => 'COALESCE(SUM(`' . $field . '`), 0)',
            $shoeFields,
        ));

        /** @var \Illuminate\Support\Collection<int, object{product_id: int|string, sizes_count: int|string|float, shoe_pairs: int|string|float, none_pairs: int|string|float}> $rows */
        $rows = DB::table((new AvailableSizes())->getTable())
            ->whereNotNull('product_id')
            ->groupBy('product_id')
            ->select([
                'product_id',
                DB::raw("($sizesExpr) as sizes_count"),
                DB::raw("($shoePairsExpr) as shoe_pairs"),
                DB::raw('COALESCE(SUM(`size_none`), 0) as none_pairs'),
            ])
            ->get();

        $levels = [];
        foreach ($rows as $row) {
            $levels[(int)$row->product_id] = self::resolve(
                (int)$row->sizes_count,
                (int)$row->shoe_pairs,
                (int)$row->none_pairs,
            );
        }

        return $levels;
    }
}
