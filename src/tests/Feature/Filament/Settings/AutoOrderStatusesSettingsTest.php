<?php

namespace Tests\Feature\Filament\Settings;

use App\Enums\Config\ConfigKey;
use App\Filament\Pages\Settings\AutoOrderStatusesSettings;
use App\Models\Admin\AdminUser;
use App\Models\Config;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AutoOrderStatusesSettingsTest extends TestCase
{
    use RefreshDatabase;

    private AdminUser $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->createSuperAdmin();
    }

    public function test_auto_order_statuses_page_can_be_rendered(): void
    {
        $this->actingAs($this->admin, 'admin');

        Livewire::test(AutoOrderStatusesSettings::class)
            ->assertSuccessful()
            ->assertSee('Включено')
            ->assertSee('Email автопарсинг (БелПочта)')
            ->assertSee('Автосмена статусов заказа и разбор писем Белпочты')
            ->assertFormSet([
                'active' => false,
                'belpost_parse_email' => false,
            ]);
    }

    public function test_auto_order_statuses_can_be_saved(): void
    {
        Cache::put('config.auto_order_statuses', ['active' => false]);

        $this->actingAs($this->admin, 'admin');

        Livewire::test(AutoOrderStatusesSettings::class)
            ->fillForm([
                'active' => true,
                'belpost_parse_email' => true,
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified('Настройки автоматических статусов сохранены');

        $config = Config::findByKey(ConfigKey::AutoOrderStatuses);

        $this->assertNotNull($config);
        $this->assertTrue($config->config['active']);
        $this->assertTrue($config->config['belpost_parse_email']);
        $this->assertFalse(Cache::has('config.auto_order_statuses'));
    }

    private function createSuperAdmin(): AdminUser
    {
        $admin = AdminUser::query()->create([
            'username' => 'auto_order_statuses_admin',
            'password' => bcrypt('secret'),
            'name' => 'Auto Order Statuses',
        ]);

        $role = Role::findOrCreate('super_admin', 'admin');
        $admin->assignRole($role);

        return $admin;
    }
}
