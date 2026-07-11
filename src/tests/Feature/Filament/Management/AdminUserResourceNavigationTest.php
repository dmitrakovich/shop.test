<?php

namespace Tests\Feature\Filament\Management;

use App\Filament\Resources\Management\AdminUsers\AdminUserResource;
use App\Filament\Resources\Management\AdminUsers\Pages\ListAdminUsers;
use App\Models\Admin\AdminUser;
use BezhanSalleh\FilamentShield\Resources\Roles\RoleResource;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminUserResourceNavigationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * TEMPORARY full-access mode: any authenticated admin reaches Filament resources.
     */
    public function test_authenticated_admin_can_access_users_and_roles_resources(): void
    {
        $admin = AdminUser::query()->create([
            'username' => 'nav_admin',
            'password' => bcrypt('secret'),
            'name' => 'Nav',
        ]);

        $this->actingAs($admin, 'admin');

        $this->assertTrue($admin->canAccessPanel(Filament::getPanel('admin')));
        $this->assertTrue($admin->isAdministrator());
        $this->assertTrue($admin->isRole('administrator'));
        $this->assertTrue(AdminUserResource::canAccess());
        $this->assertTrue(RoleResource::canAccess());

        Livewire::test(ListAdminUsers::class)
            ->assertSuccessful();
    }
}
