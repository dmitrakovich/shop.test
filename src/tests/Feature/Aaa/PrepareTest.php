<?php

namespace Tests\Feature\Aaa;

use Tests\TestCase;
use App\Facades\Currency;
use App\Jobs\SxGeoUpdateJob;
use Database\Seeders\CurrencySeeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PrepareTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_prepare()
    {
        $this->seed(CurrencySeeder::class);

        Cache::flush();
        Currency::getCurrentCurrency();

        SxGeoUpdateJob::dispatchSync();

        $this->assertTrue(true);
    }
}
