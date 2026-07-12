<?php

namespace Tests\Feature\Filament\Analytics;

use App\Enums\Analytics\AnalyticReport;
use App\Filament\Pages\Analytics\CountriesReport;
use App\Filament\Pages\Analytics\DeliveryMethodsReport;
use App\Filament\Pages\Analytics\ManagerCustomersReport;
use App\Filament\Pages\Analytics\ManagerOrderItemsReport;
use App\Filament\Pages\Analytics\OrderSourceDetailsReport;
use App\Filament\Pages\Analytics\OrderSourcesReport;
use App\Filament\Pages\Analytics\OrderTypesReport;
use App\Filament\Pages\Analytics\PaymentMethodsReport;
use App\Models\Admin\AdminUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AnalyticReportPagesTest extends TestCase
{
    use RefreshDatabase;

    private AdminUser $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->createSuperAdmin();
    }

    /**
     * @return array<string, array{0: class-string, 1: AnalyticReport}>
     */
    public static function reportPagesProvider(): array
    {
        return [
            'manager-customers' => [ManagerCustomersReport::class, AnalyticReport::ManagerCustomers],
            'manager-order-items' => [ManagerOrderItemsReport::class, AnalyticReport::ManagerOrderItems],
            'order-sources' => [OrderSourcesReport::class, AnalyticReport::OrderSources],
            'order-source-details' => [OrderSourceDetailsReport::class, AnalyticReport::OrderSourceDetails],
            'order-types' => [OrderTypesReport::class, AnalyticReport::OrderTypes],
            'payment-methods' => [PaymentMethodsReport::class, AnalyticReport::PaymentMethods],
            'delivery-methods' => [DeliveryMethodsReport::class, AnalyticReport::DeliveryMethods],
            'countries' => [CountriesReport::class, AnalyticReport::Countries],
        ];
    }

    /**
     * @param  class-string  $pageClass
     */
    #[DataProvider('reportPagesProvider')]
    public function test_analytics_report_page_can_be_rendered(string $pageClass, AnalyticReport $report): void
    {
        $this->actingAs($this->admin, 'admin');

        $component = Livewire::test($pageClass)
            ->assertSuccessful()
            ->assertSee($report->getLabel());

        $component->html();

        $period = $component->get('tableFilters.period');

        if ($report->hasDefaultDateFilter()) {
            $this->assertSame(now()->subDays(8)->toDateString(), $period['start']);
            $this->assertSame(now()->subDays(1)->toDateString(), $period['end']);
        } else {
            $this->assertNull($period['start']);
            $this->assertNull($period['end']);
        }
    }

    public function test_navigation_order_matches_legacy_menu(): void
    {
        $ordered = collect(AnalyticReport::cases())
            ->sortBy(fn (AnalyticReport $report): int => $report->navigationSort())
            ->map(fn (AnalyticReport $report): string => $report->getLabel())
            ->values()
            ->all();

        $this->assertSame([
            'Менеджер-покупатель',
            'Менеджер-товар',
            'Источники',
            'Источники PRO',
            'Тип заказа',
            'Способы оплаты',
            'Способы доставки',
            'Страна',
        ], $ordered);
    }

    private function createSuperAdmin(): AdminUser
    {
        $admin = AdminUser::query()->create([
            'username' => 'analytics_admin',
            'password' => bcrypt('secret'),
            'name' => 'Analytics Admin',
        ]);

        $role = Role::findOrCreate('super_admin', 'admin');
        $admin->assignRole($role);

        return $admin;
    }
}
