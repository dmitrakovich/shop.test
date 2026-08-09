<?php

namespace Tests\Feature\Console;

use App\Models\Audit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CleanupAuditsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_deletes_audits_older_than_six_months(): void
    {
        $old = Audit::query()->create([
            'event' => 'updated',
            'auditable_type' => 'App\\Models\\Product',
            'auditable_id' => 1,
            'old_values' => ['name' => 'Old'],
            'new_values' => ['name' => 'New'],
            'created_at' => now()->subMonths(6)->subDay(),
            'updated_at' => now()->subMonths(6)->subDay(),
        ]);

        $recent = Audit::query()->create([
            'event' => 'created',
            'auditable_type' => 'App\\Models\\Product',
            'auditable_id' => 2,
            'old_values' => [],
            'new_values' => ['name' => 'Fresh'],
            'created_at' => now()->subMonths(5),
            'updated_at' => now()->subMonths(5),
        ]);

        $this->artisan('cleanup:audits')
            ->expectsOutputToContain('CleanupAudits: удалено 1 записей.')
            ->assertSuccessful();

        $this->assertDatabaseMissing('audits', ['id' => $old->id]);
        $this->assertDatabaseHas('audits', ['id' => $recent->id]);
    }

    public function test_it_keeps_audits_exactly_six_months_old(): void
    {
        Carbon::setTestNow('2026-08-09 12:00:00');

        $boundary = Audit::query()->create([
            'event' => 'updated',
            'auditable_type' => 'App\\Models\\Product',
            'auditable_id' => 3,
            'old_values' => ['name' => 'A'],
            'new_values' => ['name' => 'B'],
            'created_at' => now()->subMonths(6),
            'updated_at' => now()->subMonths(6),
        ]);

        $this->artisan('cleanup:audits')->assertSuccessful();

        $this->assertDatabaseHas('audits', ['id' => $boundary->id]);

        Carbon::setTestNow();
    }
}
