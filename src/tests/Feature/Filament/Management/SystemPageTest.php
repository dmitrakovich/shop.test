<?php

namespace Tests\Feature\Filament\Management;

use App\Filament\Pages\Management\System;
use App\Models\Admin\AdminUser;
use Filament\Actions\Action;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SystemPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_system_page_opens_full_phpinfo_in_a_new_tab(): void
    {
        $this->actingAs($this->createSuperAdmin(), 'admin');

        $component = Livewire::test(System::class);
        $component->assertSuccessful();
        $component->assertActionExists('phpinfo');

        $page = $component->instance();
        assert($page instanceof System);

        $action = $page->getAction('phpinfo');

        $this->assertInstanceOf(Action::class, $action);
        $this->assertTrue($action->shouldOpenUrlInNewTab());
        $this->assertSame(route('filament.admin.phpinfo'), $action->getUrl());
    }

    public function test_guest_cannot_open_phpinfo(): void
    {
        $this->get(route('filament.admin.phpinfo'))
            ->assertRedirect(route('filament.admin.auth.login'));
    }

    public function test_admin_can_open_full_phpinfo(): void
    {
        $this->actingAs($this->createSuperAdmin(), 'admin');

        $this->get(route('filament.admin.phpinfo'))
            ->assertOk()
            ->assertSee('PHP Version', false)
            ->assertSee('PHP Variables', false);
    }

    public function test_clear_cache_action_clears_application_cache(): void
    {
        $commands = [];

        Artisan::partialMock()
            ->shouldReceive('call')
            ->andReturnUsing(function (string $command) use (&$commands): int {
                $commands[] = $command;

                return 0;
            });

        $this->actingAs($this->createSuperAdmin(), 'admin');

        Livewire::test(System::class)
            ->callAction('clearCache')
            ->assertNotified('Кэш сброшен');

        $this->assertSame([
            'cache:clear',
            'config:clear',
            'route:clear',
            'view:clear',
            'event:clear',
        ], $commands);
    }

    public function test_sentry_probe_action_notifies_without_failing_the_page(): void
    {
        $this->actingAs($this->createSuperAdmin(), 'admin');

        Livewire::test(System::class)
            ->callAction('testSentry')
            ->assertNotified('Тестовая ошибка отправлена в Sentry');
    }

    private function createSuperAdmin(): AdminUser
    {
        $admin = AdminUser::query()->create([
            'username' => 'system_page_admin',
            'password' => bcrypt('secret'),
            'name' => 'System Page Admin',
        ]);

        $role = Role::findOrCreate('super_admin', 'admin');
        $admin->assignRole($role);

        return $admin;
    }
}
