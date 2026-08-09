<?php

namespace Tests\Feature\Filament\Management;

use App\Filament\Resources\Management\AdminUsers\AdminUserResource;
use App\Filament\Resources\Management\Audits\AuditResource;
use App\Filament\Resources\Management\Audits\Pages\ListAudits;
use App\Models\Admin\AdminUser;
use App\Models\Audit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AuditResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_audits_list_page_loads(): void
    {
        $admin = AdminUser::query()->create([
            'username' => 'audit_admin',
            'password' => bcrypt('secret'),
            'name' => 'Audit Admin',
        ]);

        Audit::query()->create([
            'user_type' => AdminUser::class,
            'user_id' => $admin->id,
            'event' => 'updated',
            'auditable_type' => AdminUser::class,
            'auditable_id' => $admin->id,
            'old_values' => ['name' => 'Old'],
            'new_values' => ['name' => 'New'],
            'url' => 'https://shop.test/admin',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'tags' => 'admin,test',
        ]);

        $this->actingAs($admin, 'admin');

        Livewire::test(ListAudits::class)
            ->assertSuccessful();

        $this->assertDatabaseHas('audits', [
            'event' => 'updated',
            'auditable_type' => AdminUser::class,
            'auditable_id' => (string)$admin->id,
        ]);
    }

    public function test_audits_can_be_filtered_by_event(): void
    {
        $admin = AdminUser::query()->create([
            'username' => 'audit_filter_admin',
            'password' => bcrypt('secret'),
            'name' => 'Filter Admin',
        ]);

        $updated = Audit::query()->create([
            'event' => 'updated',
            'auditable_type' => AdminUser::class,
            'auditable_id' => 1,
            'old_values' => ['name' => 'A'],
            'new_values' => ['name' => 'B'],
        ]);

        $created = Audit::query()->create([
            'event' => 'created',
            'auditable_type' => AdminUser::class,
            'auditable_id' => 2,
            'old_values' => [],
            'new_values' => ['name' => 'C'],
        ]);

        $this->actingAs($admin, 'admin');

        Livewire::test(ListAudits::class)
            ->assertSuccessful()
            ->filterTable('event', 'updated')
            ->assertSuccessful();

        $this->assertSame('updated', $updated->fresh()->event->value);
        $this->assertSame('created', $created->fresh()->event->value);
    }

    public function test_audit_navigation_sits_between_users_and_horizon(): void
    {
        $usersSort = AdminUserResource::getNavigationSort();
        $auditsSort = AuditResource::getNavigationSort();

        $this->assertNotNull($usersSort);
        $this->assertNotNull($auditsSort);
        $this->assertGreaterThan($usersSort, $auditsSort);
        $this->assertLessThan(5, $auditsSort);
    }

    public function test_audit_resource_is_read_only(): void
    {
        $this->assertFalse(AuditResource::canCreate());
        $this->assertFalse(AuditResource::canDeleteAny());

        $pages = AuditResource::getPages();
        $this->assertArrayHasKey('index', $pages);
        $this->assertArrayNotHasKey('create', $pages);
        $this->assertArrayNotHasKey('edit', $pages);
    }
}
