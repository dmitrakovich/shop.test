<?php

namespace Tests\Feature\Filament\Settings;

use App\Enums\Config\ConfigKey;
use App\Filament\Pages\Settings\SendingTracksSettings;
use App\Models\Admin\AdminUser;
use App\Models\Config;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SendingTracksSettingsTest extends TestCase
{
    use RefreshDatabase;

    private AdminUser $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->createSuperAdmin();
    }

    public function test_sending_tracks_page_can_be_rendered(): void
    {
        $this->actingAs($this->admin, 'admin');

        Livewire::test(SendingTracksSettings::class)
            ->assertSuccessful()
            ->assertSee('Включена')
            ->assertSee('Города исключения')
            ->assertFormSet([
                'active' => true,
                'ignore_cities' => [],
            ]);
    }

    public function test_sending_tracks_can_be_saved(): void
    {
        Cache::put('config.sending_tracks', ['active' => true, 'ignore_cities' => []]);

        $this->actingAs($this->admin, 'admin');

        Livewire::test(SendingTracksSettings::class)
            ->fillForm([
                'active' => false,
                'ignore_cities' => ['Минск', 'ГОМЕЛЬ'],
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified('Настройки отправки треков сохранены');

        $config = Config::findByKey(ConfigKey::SendingTracks);

        $this->assertNotNull($config);
        $this->assertFalse($config->config['active']);
        $this->assertSame(['минск', 'гомель'], $config->config['ignore_cities']);
        $this->assertFalse(Cache::has('config.sending_tracks'));
    }

    private function createSuperAdmin(): AdminUser
    {
        $admin = AdminUser::query()->create([
            'username' => 'sending_tracks_admin',
            'password' => bcrypt('secret'),
            'name' => 'Sending Tracks',
        ]);

        $role = Role::findOrCreate('super_admin', 'admin');
        $admin->assignRole($role);

        return $admin;
    }
}
