<?php

namespace Tests\Unit\Services\Feeds;

use App\Models\Product;
use App\Models\Size;
use App\Services\Feeds\SizeRangeFilter;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SizeRangeFilterTest extends TestCase
{
    /**
     * @param  list<int>  $sizeIds
     */
    #[DataProvider('eligibilityProvider')]
    public function test_eligible(array $sizeIds, bool $expected, bool $trashed = false): void
    {
        $product = new Product();
        if ($trashed) {
            $product->deleted_at = Carbon::parse('2026-01-01');
        }
        $product->setRelation('sizes', new EloquentCollection(
            array_map(function (int $id): Size {
                $size = new Size();
                $size->id = $id;

                return $size;
            }, $sizeIds),
        ));

        $this->assertSame($expected, SizeRangeFilter::eligible($product));
    }

    /**
     * @return array<string, array{list<int>, bool, bool}>
     */
    public static function eligibilityProvider(): array
    {
        return [
            'one shoe size' => [[5], false, false],
            'two shoe sizes' => [[5, 6], false, false],
            'three shoe sizes' => [[5, 6, 7], true, false],
            'one-size accessory' => [[Size::ONE_SIZE_ID], true, false],
            'one-size plus two shoe sizes' => [[Size::ONE_SIZE_ID, 5, 6], false, false],
            'one-size plus three shoe sizes' => [[Size::ONE_SIZE_ID, 5, 6, 7], true, false],
            'no sizes' => [[], true, false],
            'unpublished with full size range' => [[5, 6, 7], false, true],
            'unpublished one-size accessory' => [[Size::ONE_SIZE_ID], false, true],
            'unpublished with no sizes' => [[], false, true],
        ];
    }
}
