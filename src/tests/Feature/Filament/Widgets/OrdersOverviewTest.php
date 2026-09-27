<?php

namespace Tests\Feature\Filament\Widgets;

use App\Filament\Widgets\OrdersChart;
use App\Models\Admin\AdminUser;
use App\Models\Orders\Order;
use App\ValueObjects\Phone;
use Filament\Facades\Filament;
use Filament\Pages\Dashboard;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use ReflectionMethod;
use Tests\TestCase;

class OrdersOverviewTest extends TestCase
{
    use RefreshDatabase;

    private AdminUser $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = AdminUser::query()->create([
            'username' => 'orders_widget_admin',
            'password' => bcrypt('secret'),
            'name' => 'Orders',
        ]);
    }

    public function test_dashboard_includes_filament_info_and_orders_chart(): void
    {
        $this->actingAs($this->admin, 'admin');

        $widgets = Filament::getPanel('admin')->getWidgets();

        $this->assertContains(AccountWidget::class, $widgets);
        $this->assertContains(FilamentInfoWidget::class, $widgets);
        $this->assertContains(OrdersChart::class, $widgets);

        /** @var Testable<Dashboard> $dashboard */
        $dashboard = Livewire::test(Dashboard::class);
        $dashboard->assertSuccessful();
        $dashboard->assertSee('https://filamentphp.com');
        $dashboard->assertSee('По дате создания, последние 60 дней');
    }

    public function test_orders_chart_counts_orders_by_created_at_day(): void
    {
        $this->actingAs($this->admin, 'admin');

        $before = $this->series();

        $this->createOrder(now());
        $this->createOrder(now());
        $this->createOrder(now()->subDays(3));
        $this->createOrder(now()->subDays(40));
        $this->createOrder(now()->subDays(70));

        $after = $this->series();

        $this->assertCount(60, $after);
        $this->assertSame($before[today()->format('d.m')] + 2, $after[today()->format('d.m')]);
        $this->assertSame($before[today()->subDays(3)->format('d.m')] + 1, $after[today()->subDays(3)->format('d.m')]);
        $this->assertSame($before[today()->subDays(40)->format('d.m')] + 1, $after[today()->subDays(40)->format('d.m')]);
        $this->assertSame(array_sum($before) + 4, array_sum($after));
    }

    /**
     * @return array<string, int>
     */
    private function series(): array
    {
        /** @var Testable<OrdersChart> $component */
        $component = Livewire::test(OrdersChart::class);
        $component->assertSuccessful();
        $component->assertSee('data-chart-type="line"', false);
        $component->assertSee('max-height: 540px', false);

        $method = new ReflectionMethod(OrdersChart::class, 'getData');
        /** @var array{datasets: list<array{data: list<int>}>, labels: list<string>} $data */
        $data = $method->invoke($component->instance());

        $series = [];
        foreach ($data['labels'] as $index => $label) {
            $series[$label] = $data['datasets'][0]['data'][$index];
        }

        return $series;
    }

    private function createOrder(Carbon $createdAt): void
    {
        Order::withoutEvents(function () use ($createdAt): void {
            Order::query()->create([
                'first_name' => 'Тест',
                'phone' => Phone::fromRawString('+375291112233'),
                'total_price' => 100,
                'currency' => 'BYN',
                'rate' => 1,
                'created_at' => $createdAt,
            ]);
        });
    }
}
