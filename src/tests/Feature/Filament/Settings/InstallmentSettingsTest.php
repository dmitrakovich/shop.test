<?php

namespace Tests\Feature\Filament\Settings;

use App\Filament\Pages\Settings\InstallmentSettings;
use App\Models\Admin\AdminUser;
use App\Models\Config;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InstallmentSettingsTest extends TestCase
{
    use RefreshDatabase;

    private AdminUser $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->createSuperAdmin();
    }

    public function test_installment_settings_page_can_be_rendered(): void
    {
        Config::query()->create([
            'key' => 'installment',
            'config' => [
                'min_price' => '100.00',
                'min_price_3_parts' => '200.00',
            ],
        ]);

        $this->actingAs($this->admin, 'admin');

        $component = Livewire::test(InstallmentSettings::class);
        $component->assertSuccessful();
        $component->assertFormSet([
            'min_price' => '100.00',
            'min_price_3_parts' => '200.00',
        ]);
    }

    public function test_installment_settings_can_be_saved(): void
    {
        Config::query()->create([
            'key' => 'installment',
            'config' => [
                'min_price' => '100.00',
                'min_price_3_parts' => '200.00',
            ],
        ]);

        Cache::put('config.installment', ['min_price' => '100.00']);

        $this->actingAs($this->admin, 'admin');

        Livewire::test(InstallmentSettings::class)
            ->fillForm([
                'min_price' => '150.50',
                'min_price_3_parts' => '300.00',
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $config = Config::query()->find('installment');

        $this->assertNotNull($config);
        $this->assertSame('150.5', (string)$config->config['min_price']);
        $this->assertSame('300', (string)$config->config['min_price_3_parts']);
        $this->assertFalse(Cache::has('config.installment'));
    }

    private function createSuperAdmin(): AdminUser
    {
        $admin = AdminUser::query()->create([
            'username' => 'settings_admin',
            'password' => bcrypt('secret'),
            'name' => 'Settings',
        ]);

        $role = Role::findOrCreate('super_admin', 'admin');
        $admin->assignRole($role);

        return $admin;
    }
}
