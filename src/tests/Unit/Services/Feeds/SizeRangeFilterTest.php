<?php

namespace Tests\Unit\Services\Feeds;

use App\Models\Product;
use App\Models\Size;
use App\Services\Feeds\SizeRangeFilter;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SizeRangeFilterTest extends TestCase
{
    /**
     * @param  list<int>  $sizeIds
     */
    #[DataProvider('eligibilityProvider')]
    public function test_eligible(array $sizeIds, bool $expected): void
    {
        $product = new Product();
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
     * @return array<string, array{list<int>, bool}>
     */
    public static function eligibilityProvider(): array
    {
        return [
            'one shoe size' => [[5], false],
            'two shoe sizes' => [[5, 6], false],
            'three shoe sizes' => [[5, 6, 7], true],
            'one-size accessory' => [[Size::ONE_SIZE_ID], true],
            'one-size plus two shoe sizes' => [[Size::ONE_SIZE_ID, 5, 6], false],
            'one-size plus three shoe sizes' => [[Size::ONE_SIZE_ID, 5, 6, 7], true],
            'no sizes' => [[], true],
        ];
    }
}
