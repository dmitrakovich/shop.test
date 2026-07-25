<?php

namespace Tests;

use App\Services\CurrencyService;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Seed the entire database once per PHPUnit process when using RefreshDatabase.
     */
    protected bool $seed = true;

    protected function setUp(): void
    {
        parent::setUp();

        // CurrencyService caches currencies at construct; re-bind after seeding.
        $this->app->forgetInstance(CurrencyService::class);
    }
}
