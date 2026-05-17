<?php

namespace Tests\Unit;

use App\Enums\Ads\BannerPosition;
use Tests\TestCase;

class BannerPositionTest extends TestCase
{
    public function test_from_form_state_accepts_enum_instance(): void
    {
        $this->assertSame(
            BannerPosition::INDEX_DOUBLE,
            BannerPosition::fromFormState(BannerPosition::INDEX_DOUBLE),
        );
    }

    public function test_from_form_state_accepts_backed_enum_value(): void
    {
        $this->assertSame(
            BannerPosition::INDEX_CATEGORY,
            BannerPosition::fromFormState('index_category'),
        );
    }

    public function test_from_form_state_falls_back_to_default_for_create_when_value_is_empty(): void
    {
        $this->assertSame(
            BannerPosition::defaultForCreate(),
            BannerPosition::fromFormState(null),
        );
    }
}
