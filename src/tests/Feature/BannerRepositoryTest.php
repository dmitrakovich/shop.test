<?php

namespace Tests\Feature;

use App\Enums\Ads\BannerPosition;
use App\Models\Ads\Banner;
use App\Repositories\BannerRepository;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;

class BannerRepositoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_it_builds_cache_key_with_position_and_version(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-01-01 00:00:00'));

        $position = BannerPosition::INDEX_MAIN;

        $builderEnd = Mockery::mock();
        $builderEnd->shouldReceive('where')->with('position', $position)->andReturnSelf();
        $builderEnd->shouldReceive('active')->andReturnSelf();
        $builderEnd->shouldReceive('whereNotNull')->with('end_datetime')->andReturnSelf();
        $builderEnd->shouldReceive('min')->with('end_datetime')->andReturn('2026-01-01 00:02:00');

        $builderStart = Mockery::mock();
        $builderStart->shouldReceive('where')->with('position', $position)->andReturnSelf();
        $builderStart->shouldReceive('where')->with('active', true)->andReturnSelf();
        $builderStart->shouldReceive('whereNotNull')->with('start_datetime')->andReturnSelf();
        $builderStart->shouldReceive('where')
            ->with('start_datetime', '>', Mockery::type(Carbon::class))
            ->andReturnSelf();
        $builderStart->shouldReceive('min')->with('start_datetime')->andReturn('2026-01-01 00:05:00');

        $banner = Mockery::mock(Banner::class);
        $banner->shouldReceive('newQuery')->twice()->andReturn($builderEnd, $builderStart);

        $cache = Mockery::mock(CacheRepository::class);
        $cache->shouldReceive('get')->with('banners.cache_version', 1)->andReturn(5);
        $cache->shouldReceive('remember')
            ->with(
                'banners.v5.position.index_main',
                120,
                Mockery::type('callable')
            )
            ->andReturn(new Collection());

        $repo = new BannerRepository($banner, $cache);

        $this->assertInstanceOf(Collection::class, $repo->getBannersByPosition($position));
    }

    public function test_it_uses_min_ttl_when_next_change_is_too_close(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-01-01 00:00:00'));

        $position = BannerPosition::INDEX_TOP;

        $builderEnd = Mockery::mock();
        $builderEnd->shouldReceive('where')->with('position', $position)->andReturnSelf();
        $builderEnd->shouldReceive('active')->andReturnSelf();
        $builderEnd->shouldReceive('whereNotNull')->with('end_datetime')->andReturnSelf();
        $builderEnd->shouldReceive('min')->with('end_datetime')->andReturn(null);

        $builderStart = Mockery::mock();
        $builderStart->shouldReceive('where')->with('position', $position)->andReturnSelf();
        $builderStart->shouldReceive('where')->with('active', true)->andReturnSelf();
        $builderStart->shouldReceive('whereNotNull')->with('start_datetime')->andReturnSelf();
        $builderStart->shouldReceive('where')
            ->with('start_datetime', '>', Mockery::type(Carbon::class))
            ->andReturnSelf();
        $builderStart->shouldReceive('min')->with('start_datetime')->andReturn('2026-01-01 00:00:20');

        $banner = Mockery::mock(Banner::class);
        $banner->shouldReceive('newQuery')->twice()->andReturn($builderEnd, $builderStart);

        $cache = Mockery::mock(CacheRepository::class);
        $cache->shouldReceive('get')->with('banners.cache_version', 1)->andReturn(1);
        $cache->shouldReceive('remember')
            ->with(
                'banners.v1.position.index_top',
                30,
                Mockery::type('callable')
            )
            ->andReturn(new Collection());

        $repo = new BannerRepository($banner, $cache);

        $this->assertInstanceOf(Collection::class, $repo->getBannersByPosition($position));
    }
}
