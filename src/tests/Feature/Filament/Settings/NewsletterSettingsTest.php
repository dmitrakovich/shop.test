<?php

namespace Tests\Feature\Filament\Settings;

use App\Enums\Config\ConfigKey;
use App\Filament\Pages\Settings\NewsletterSettings;
use App\Models\Admin\AdminUser;
use App\Models\Config;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NewsletterSettingsTest extends TestCase
{
    use RefreshDatabase;

    private AdminUser $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->createSuperAdmin();
    }

    public function test_newsletter_page_can_be_rendered(): void
    {
        $this->actingAs($this->admin, 'admin');

        Livewire::test(NewsletterSettings::class)
            ->assertSuccessful()
            ->assertSee('Включена')
            ->assertSee('Количество дней после регистрации до')
            ->assertSee('Количество дней после регистрации от')
            ->assertFormSet([
                'active' => true,
                'to_days' => 30,
                'from_days' => 5,
            ]);
    }

    public function test_newsletter_can_be_saved(): void
    {
        Cache::put('config.newsletter_register', ['active' => true, 'to_days' => 30, 'from_days' => 5]);

        $this->actingAs($this->admin, 'admin');

        Livewire::test(NewsletterSettings::class)
            ->fillForm([
                'active' => false,
                'to_days' => 21,
                'from_days' => 7,
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified('Настройки рассылки сохранены');

        $config = Config::findByKey(ConfigKey::NewsletterRegister);

        $this->assertNotNull($config);
        $this->assertFalse($config->config['active']);
        $this->assertSame(21, $config->config['to_days']);
        $this->assertSame(7, $config->config['from_days']);
        $this->assertFalse(Cache::has('config.newsletter_register'));
    }

    private function createSuperAdmin(): AdminUser
    {
        $admin = AdminUser::query()->create([
            'username' => 'newsletter_settings_admin',
            'password' => bcrypt('secret'),
            'name' => 'Newsletter Settings',
        ]);

        $role = Role::findOrCreate('super_admin', 'admin');
        $admin->assignRole($role);

        return $admin;
    }
}
