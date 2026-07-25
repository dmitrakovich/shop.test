<?php

namespace Tests\Feature\Filament\Settings;

use App\Enums\Feedback\ReviewDiscountType;
use App\Filament\Pages\Settings\FeedbackSettings;
use App\Models\Admin\AdminUser;
use App\Models\Config;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FeedbackSettingsTest extends TestCase
{
    use RefreshDatabase;

    private AdminUser $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->createSuperAdmin();
    }

    public function test_feedback_settings_page_can_be_rendered(): void
    {
        $this->actingAs($this->admin, 'admin');

        $component = Livewire::test(FeedbackSettings::class);
        $component->assertSuccessful();
        $component->assertFormSet([
            'discount' => [
                ReviewDiscountType::Photo->value => [
                    'BYN' => '10',
                    'USD' => '5',
                    'KZT' => '1500',
                    'RUB' => '350',
                ],
                ReviewDiscountType::Video->value => [
                    'BYN' => '20',
                    'USD' => '10',
                    'KZT' => '3000',
                    'RUB' => '700',
                ],
            ],
            'send_after' => '72',
        ]);
    }

    public function test_feedback_settings_can_be_saved(): void
    {
        Cache::put('config.feedback', ['discount' => ['photo' => ['BYN' => '10']]]);

        $this->actingAs($this->admin, 'admin');

        Livewire::test(FeedbackSettings::class)
            ->fillForm([
                'discount' => [
                    ReviewDiscountType::Photo->value => [
                        'BYN' => '12',
                        'USD' => '6',
                        'KZT' => '1600',
                        'RUB' => '400',
                    ],
                    ReviewDiscountType::Video->value => [
                        'BYN' => '25',
                        'USD' => '12',
                        'KZT' => '3500',
                        'RUB' => '800',
                    ],
                ],
                'send_after' => '48',
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $config = Config::query()->find('feedback');

        $this->assertNotNull($config);
        $this->assertSame('12', (string)$config->config['discount'][ReviewDiscountType::Photo->value]['BYN']);
        $this->assertSame('25', (string)$config->config['discount'][ReviewDiscountType::Video->value]['BYN']);
        $this->assertSame('48', (string)$config->config['send_after']);
        $this->assertFalse(Cache::has('config.feedback'));
    }

    private function createSuperAdmin(): AdminUser
    {
        $admin = AdminUser::query()->create([
            'username' => 'feedback_settings_admin',
            'password' => bcrypt('secret'),
            'name' => 'Feedback Settings',
        ]);

        $role = Role::findOrCreate('super_admin', 'admin');
        $admin->assignRole($role);

        return $admin;
    }
}
