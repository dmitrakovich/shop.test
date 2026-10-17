<?php

namespace Tests\Feature\Filament;

use App\Enums\Filament\NavGroup;
use App\Models\Admin\AdminUser;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OldAdminNavigationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs($this->createSuperAdmin(), 'admin');

        $this->get(Filament::getPanel('admin')->getUrl())->assertOk();
    }

    public function test_old_admin_links_are_placed_in_their_target_groups(): void
    {
        $this->assertSame([
            'Заказы' => url('old-admin/orders'),
            'Товары в заказах' => url('old-admin/order-items'),
            'Движение единиц' => url('old-admin/logs/order-item-statuses'),
            'Оффлайн продажи' => url('old-admin/orders/offline'),
            'Платежи' => url('old-admin/bookkeeping/payments'),
        ], $this->groupItemUrls(NavGroup::Orders));

        $departures = $this->groupItemUrls(NavGroup::Departures);
        $this->assertSame(['Заказы на отправку', 'Партии Белпочта', 'Трек-номера'], array_keys($departures));
        $this->assertSame(url('old-admin/departures/order-to-send'), $departures['Заказы на отправку']);
        $this->assertSame(url('old-admin/departures/track-numbers'), $departures['Трек-номера']);

        $this->assertSame([
            'Склад' => url('old-admin/automation/stock'),
            'Новые товары из 1С' => url('old-admin/automation/inventory'),
            'Лог наличия' => url('old-admin/logs/inventory'),
        ], $this->groupItemUrls(NavGroup::Inventory));
    }

    public function test_old_admin_links_open_in_new_tab_with_badge(): void
    {
        $oldAdminItems = $this->navigationItems()
            ->filter(fn (NavigationItem $item): bool => str_starts_with((string)$item->getUrl(), url('old-admin/')));

        $this->assertCount(10, $oldAdminItems);

        foreach ($oldAdminItems as $item) {
            $this->assertTrue($item->shouldOpenUrlInNewTab(), $item->getLabel());
            $this->assertSame('old', $item->getBadge(), $item->getLabel());
        }
    }

    public function test_obsolete_old_admin_sections_are_not_linked(): void
    {
        $urls = $this->navigationItems()->map(fn (NavigationItem $item): ?string => $item->getUrl());

        $this->assertNotContains(url('old-admin/departures/batches'), $urls);
        $this->assertNotContains(url('old-admin/offline/displacement'), $urls);
        $this->assertNotContains(url('old-admin/automation/inventory-blacklist'), $urls);
        $this->assertSame(1, $urls->filter(fn (?string $url): bool => $url === url('old-admin/automation/stock'))->count());
        $this->assertNotContains('Старая админка', $this->groupLabels());
        $this->assertContains('Наличие / 1С', $this->groupLabels());
    }

    public function test_orders_and_departures_groups_come_first(): void
    {
        $this->assertSame(
            [NavGroup::Orders->getLabel(), NavGroup::Departures->getLabel()],
            array_slice($this->groupLabels(), 0, 2),
        );
    }

    /**
     * @return array<string, string|null>
     */
    private function groupItemUrls(NavGroup $navGroup): array
    {
        $group = collect(Filament::getNavigation())
            ->first(fn (NavigationGroup $group): bool => $group->getLabel() === $navGroup->getLabel());

        $this->assertNotNull($group, $navGroup->getLabel());

        return collect($group->getItems())
            ->mapWithKeys(fn (NavigationItem $item): array => [$item->getLabel() => $item->getUrl()])
            ->all();
    }

    /**
     * @return Collection<int, NavigationItem>
     */
    private function navigationItems(): Collection
    {
        return collect(Filament::getNavigation())
            ->flatMap(fn (NavigationGroup $group): array => collect($group->getItems())->all());
    }

    /**
     * @return array<int, string>
     */
    private function groupLabels(): array
    {
        return collect(Filament::getNavigation())
            ->map(fn (NavigationGroup $group): ?string => $group->getLabel())
            ->filter()
            ->values()
            ->all();
    }

    private function createSuperAdmin(): AdminUser
    {
        $admin = AdminUser::query()->create([
            'username' => 'old_admin_nav_admin',
            'password' => bcrypt('secret'),
            'name' => 'Old Admin Nav',
        ]);

        $admin->assignRole(Role::findOrCreate('super_admin', 'admin'));

        return $admin;
    }
}
