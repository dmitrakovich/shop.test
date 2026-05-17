<?php

namespace Tests\Unit;

use App\Enums\Ads\BannerPosition;
use App\Enums\Ads\BannerType;
use App\Models\Ads\Banner;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionMethod;
use Tests\TestCase;

class BannerTest extends TestCase
{
    #[DataProvider('desktopOnlyPositionsProvider')]
    public function test_saving_syncs_mobile_type_with_desktop_type_for_desktop_only_positions(
        BannerPosition $position,
    ): void {
        $banner = new Banner([
            'position' => $position,
            'desktop_type' => BannerType::VIDEO,
            'mobile_type' => BannerType::IMAGE,
        ]);

        $this->fireSavingEvent($banner);

        $this->assertSame(BannerType::VIDEO, $banner->mobile_type);
    }

    public function test_saving_keeps_mobile_type_for_positions_with_mobile_creatives(): void
    {
        $banner = new Banner([
            'position' => BannerPosition::INDEX_MAIN,
            'desktop_type' => BannerType::VIDEO,
            'mobile_type' => BannerType::IMAGE,
        ]);

        $this->fireSavingEvent($banner);

        $this->assertSame(BannerType::IMAGE, $banner->mobile_type);
    }

    public function test_saving_syncs_mobile_type_when_desktop_type_changes_on_desktop_only_position(): void
    {
        $banner = new Banner([
            'position' => BannerPosition::INDEX_DOUBLE,
            'desktop_type' => BannerType::IMAGE,
            'mobile_type' => BannerType::IMAGE,
        ]);

        $this->fireSavingEvent($banner);

        $banner->desktop_type = BannerType::VIDEO;

        $this->fireSavingEvent($banner);

        $this->assertSame(BannerType::VIDEO, $banner->mobile_type);
    }

    /**
     * @return array<string, array{0: BannerPosition}>
     */
    public static function desktopOnlyPositionsProvider(): array
    {
        return [
            'index_double' => [BannerPosition::INDEX_DOUBLE],
            'index_category' => [BannerPosition::INDEX_CATEGORY],
        ];
    }

    private function fireSavingEvent(Banner $banner): void
    {
        (new ReflectionMethod($banner, 'fireModelEvent'))->invoke($banner, 'saving');
    }
}
