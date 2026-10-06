<?php

namespace Tests\Unit\Models\Feeds;

use App\Models\Feeds\GoogleAdsXml;
use Tests\TestCase;

class GoogleAdsXmlTest extends TestCase
{
    public function test_reuses_google_merchant_view(): void
    {
        $feed = new GoogleAdsXml();

        $this->assertSame('google_ads', $feed->getKey());
        $this->assertSame('google', $feed->getViewName());
    }
}
