<?php

namespace Tests\Feature\Filament\Settings;

use App\Enums\Filament\NavGroup;
use App\Filament\Clusters\Config\ConfigCluster;
use App\Filament\Pages\Settings\AutoOrderStatusesSettings;
use App\Filament\Pages\Settings\FeedbackSettings;
use App\Filament\Pages\Settings\InstallmentSettings;
use App\Filament\Pages\Settings\NewsletterSettings;
use App\Filament\Pages\Settings\SendingTracksSettings;
use App\Models\Admin\AdminUser;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Support\Enums\Width;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ConfigNavigationTest extends TestCase
{
    use RefreshDatabase;

    private AdminUser $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->createSuperAdmin();
    }

    public function test_small_config_pages_live_in_the_config_cluster(): void
    {
        $this->actingAs($this->admin, 'admin');

        $page = Livewire::test(AutoOrderStatusesSettings::class)
            ->assertSuccessful()
            ->assertSee('Автосмена статусов заказа и разбор писем Белпочты');

        $this->assertSame(SubNavigationPosition::Start, ConfigCluster::getSubNavigationPosition());
        $this->assertSame(SubNavigationPosition::Start, AutoOrderStatusesSettings::getSubNavigationPosition());

        $settingsGroup = collect(Filament::getNavigation())
            ->first(fn (NavigationGroup $group): bool => $group->getLabel() === NavGroup::Settings->getLabel());

        $this->assertNotNull($settingsGroup);

        $settingsLabels = collect($settingsGroup->getItems())
            ->map(fn (NavigationItem $item): string => $item->getLabel())
            ->all();

        $this->assertContains('Конфиг', $settingsLabels);
        $this->assertNotContains('Рассрочка', $settingsLabels);
        $this->assertNotContains('Отзывы', $settingsLabels);
        $this->assertNotContains('Автоматические статусы заказа', $settingsLabels);
        $this->assertNotContains('Отправка треков', $settingsLabels);
        $this->assertNotContains('Рассылка для зарегистрированных', $settingsLabels);

        $configItem = collect($settingsGroup->getItems())
            ->first(fn (NavigationItem $item): bool => $item->getLabel() === 'Конфиг');

        $this->assertNotNull($configItem);
        $this->assertSame(ConfigCluster::getUrl(), $configItem->getUrl());

        $instance = $page->instance();
        $this->assertInstanceOf(AutoOrderStatusesSettings::class, $instance);
        $this->assertSame(Width::Full, $instance->getMaxContentWidth());

        $subNavLabels = [];

        foreach ($instance->getCachedSubNavigation() as $group) {
            foreach ($group->getItems() as $item) {
                $subNavLabels[] = $item->getLabel();
                $this->assertNotNull($item->getIcon());
            }
        }

        $this->assertSame([
            'Рассрочка',
            'Отзывы',
            'Автоматические статусы заказа',
            'Отправка треков',
            'Рассылка для зарегистрированных',
        ], $subNavLabels);

        $this->get(ConfigCluster::getUrl())
            ->assertRedirect(InstallmentSettings::getUrl());

        $this->get(FeedbackSettings::getUrl())->assertOk();
        $this->get(SendingTracksSettings::getUrl())->assertOk();
        $this->get(NewsletterSettings::getUrl())->assertOk();
    }

    private function createSuperAdmin(): AdminUser
    {
        $admin = AdminUser::query()->create([
            'username' => 'config_nav_admin',
            'password' => bcrypt('secret'),
            'name' => 'Config Nav',
        ]);

        $admin->assignRole(Role::findOrCreate('super_admin', 'admin'));

        return $admin;
    }
}
