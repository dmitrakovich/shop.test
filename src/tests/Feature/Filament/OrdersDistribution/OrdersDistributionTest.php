<?php

namespace Tests\Feature\Filament\OrdersDistribution;

use App\Enums\Config\ConfigKey;
use App\Enums\Order\OrderTypeEnum;
use App\Filament\Pages\OrdersDistribution\DistributionSettings;
use App\Filament\Pages\OrdersDistribution\DistributionStatistic;
use App\Filament\Pages\OrdersDistribution\WorkSchedulePage;
use App\Filament\Resources\OrdersDistribution\Logs\Pages\ListOrderDistributionLogs;
use App\Models\Admin\AdminUser;
use App\Models\Config;
use App\Models\Logs\OrderDistributionLog;
use App\Models\Orders\Order;
use App\Models\WorkSchedule;
use App\Services\Order\OrdersDistributionStatistic;
use App\ValueObjects\Phone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OrdersDistributionTest extends TestCase
{
    use RefreshDatabase;

    public function test_distribution_settings_can_be_saved(): void
    {
        $manager = $this->createManager('settings_manager', 'Анна', 'Иванова');

        $this->actingAs($this->createSuperAdmin('distribution_settings_admin'), 'admin');

        Livewire::test(DistributionSettings::class)
            ->assertSuccessful()
            ->fillForm([
                'active' => true,
                'schedule' => [
                    [
                        'admin_user_id' => $manager->id,
                        'time_from_even' => '09:00',
                        'time_to_even' => '18:00',
                        'time_from_odd' => '10:00',
                        'time_to_odd' => '17:00',
                    ],
                ],
            ])
            ->assertSee('Время работы (четные дни)')
            ->assertSee('Время работы (нечетные дни)')
            ->assertSeeHtml('fi-fo-table-repeater')
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified('Настройки распределения сохранены');

        $config = Config::findByKey(ConfigKey::DistribOrderSetup);

        $this->assertNotNull($config);
        $this->assertTrue($config->config['active']);
        $this->assertSame($manager->id, $config->config['schedule'][0]['admin_user_id']);
        $this->assertSame('09:00', $config->config['schedule'][0]['time_from_even']);
        $this->assertSame('18:00', $config->config['schedule'][0]['time_to_even']);
        $this->assertFalse(Cache::has('config.distrib_order_setup'));
    }

    public function test_work_schedule_saves_checked_days_for_the_open_month(): void
    {
        $manager = $this->createManager('schedule_manager', 'Борис', 'Петров');
        $this->saveSchedule($manager);
        $date = now()->startOfMonth()->toDateString();

        $this->actingAs($this->createSuperAdmin('distribution_schedule_admin'), 'admin');

        Livewire::test(WorkSchedulePage::class)
            ->assertSuccessful()
            ->assertSee('Петров Б.')
            ->assertSeeHtml('work-schedule-table')
            ->set('shifts.' . $date . '.' . $manager->id, true)
            ->call('save')
            ->assertNotified('График работы сохранён');

        $this->assertTrue(
            WorkSchedule::query()->where('admin_user_id', $manager->id)->whereDate('date', $date)->exists(),
        );

        Livewire::test(WorkSchedulePage::class)
            ->set('shifts.' . $date . '.' . $manager->id, false)
            ->call('save');

        $this->assertFalse(
            WorkSchedule::query()->where('admin_user_id', $manager->id)->whereDate('date', $date)->exists(),
        );
    }

    public function test_work_schedule_keeps_saved_days_when_changing_months(): void
    {
        $this->travelTo(Carbon::parse('2026-10-15 12:00:00'));

        $manager = $this->createManager('schedule_month_manager', 'Борис', 'Петров');
        $this->saveSchedule($manager);

        WorkSchedule::query()->create([
            'admin_user_id' => $manager->id,
            'date' => '2026-09-15',
        ]);
        WorkSchedule::query()->create([
            'admin_user_id' => $manager->id,
            'date' => '2026-08-15',
        ]);
        WorkSchedule::query()->create([
            'admin_user_id' => $manager->id,
            'date' => '2026-08-31',
        ]);

        $this->actingAs($this->createSuperAdmin('distribution_month_admin'), 'admin');

        Livewire::test(WorkSchedulePage::class)
            ->assertSet('month', '2026-10')
            ->assertCount('days', 31)
            ->assertSeeHtml('wire:key="work-schedule-2026-10"')
            ->call('previousMonth')
            ->assertSet('month', '2026-09')
            ->assertCount('days', 30)
            ->assertSet('shifts.2026-09-15.'.$manager->id, true)
            ->assertSet('shifts.2026-09-01.'.$manager->id, false)
            ->assertSeeHtml('wire:key="work-schedule-2026-09"')
            ->assertSeeHtml('wire:key="shift-2026-09-15-'.$manager->id.'"')
            ->call('previousMonth')
            ->assertSet('month', '2026-08')
            ->assertCount('days', 31)
            ->assertSet('shifts.2026-08-15.'.$manager->id, true)
            ->assertSet('shifts.2026-08-31.'.$manager->id, true)
            ->assertSet('shifts.2026-08-01.'.$manager->id, false)
            ->assertSeeHtml('wire:key="work-schedule-2026-08"')
            ->call('nextMonth')
            ->assertSet('month', '2026-09')
            ->assertCount('days', 30)
            ->assertSet('shifts.2026-09-15.'.$manager->id, true)
            ->assertSet('shifts.2026-08-31.'.$manager->id, null);
    }

    public function test_distribution_log_lists_entries(): void
    {
        $manager = $this->createManager('log_manager', 'Ольга', 'Сидорова');
        $order = $this->createOrder($manager, OrderTypeEnum::DESKTOP);

        OrderDistributionLog::query()->create([
            'order_id' => $order->id,
            'admin_user_id' => $manager->id,
            'action' => 'по ссылке',
        ]);

        $this->actingAs($this->createSuperAdmin('distribution_log_admin'), 'admin');

        Livewire::test(ListOrderDistributionLogs::class)
            ->assertSuccessful()
            ->assertSee('по ссылке')
            ->assertSee((string)$order->id)
            ->assertSee('Сидорова О.');
    }

    public function test_statistic_counts_distributed_and_manual_orders(): void
    {
        $first = $this->createManager('stat_first', 'Анна', 'Иванова');
        $second = $this->createManager('stat_second', 'Борис', 'Петров');

        $distributedManual = $this->createOrder($first, OrderTypeEnum::MANAGER);
        $accepted = $this->createOrder($first, OrderTypeEnum::DESKTOP);
        $distributed = $this->createOrder($second, OrderTypeEnum::DESKTOP);

        OrderDistributionLog::query()->create([
            'order_id' => $distributedManual->id,
            'admin_user_id' => $first->id,
            'action' => 'по очереди №1',
        ]);
        OrderDistributionLog::query()->create([
            'order_id' => $distributed->id,
            'admin_user_id' => $second->id,
            'action' => 'по очереди №1',
        ]);

        $rows = app(OrdersDistributionStatistic::class)
            ->rows(now()->startOfDay(), now()->endOfDay())
            ->keyBy('__key');

        $this->assertSame([
            'instance_name' => 'Иванова А.',
            'distribution_count' => 1,
            'distribution_percentage' => 50.0,
            'created_manually' => 1,
            'accepted_manually' => 1,
            'total_count' => 2,
        ], $this->statisticRow($rows[(string)$first->id]));

        $this->assertSame([
            'instance_name' => 'Петров Б.',
            'distribution_count' => 1,
            'distribution_percentage' => 50.0,
            'created_manually' => 0,
            'accepted_manually' => 0,
            'total_count' => 1,
        ], $this->statisticRow($rows[(string)$second->id]));

        $this->actingAs($this->createSuperAdmin('distribution_statistic_admin'), 'admin');

        Excel::fake();

        Livewire::test(DistributionStatistic::class)
            ->assertSuccessful()
            ->assertSee('Иванова А.')
            ->assertSee('Петров Б.')
            ->callTableAction('export');

        Excel::assertDownloaded('Статистика распределения.xlsx');
    }

    private function createSuperAdmin(string $username): AdminUser
    {
        $admin = AdminUser::query()->create([
            'username' => $username,
            'password' => bcrypt('secret'),
            'name' => 'Admin',
        ]);

        $role = Role::findOrCreate('super_admin', 'admin');
        $admin->assignRole($role);

        return $admin;
    }

    private function createManager(string $username, string $name, string $lastName): AdminUser
    {
        return AdminUser::query()->create([
            'username' => $username,
            'password' => bcrypt('secret'),
            'name' => $name,
            'user_last_name' => $lastName,
        ]);
    }

    private function saveSchedule(AdminUser $manager): void
    {
        Config::findByKeyOrFail(ConfigKey::DistribOrderSetup)->update([
            'config' => [
                'active' => true,
                'schedule' => [
                    [
                        'admin_user_id' => $manager->id,
                        'time_from_even' => '09:00',
                        'time_to_even' => '18:00',
                        'time_from_odd' => '09:00',
                        'time_to_odd' => '18:00',
                    ],
                ],
            ],
        ]);

        Cache::forget('config.distrib_order_setup');
    }

    private function createOrder(AdminUser $manager, OrderTypeEnum $type): Order
    {
        return Order::withoutEvents(fn (): Order => Order::query()->create([
            'first_name' => 'Тест',
            'phone' => Phone::fromRawString('+375291112233'),
            'total_price' => 100,
            'currency' => 'BYN',
            'rate' => 1,
            'admin_id' => $manager->id,
            'order_type' => $type,
            'created_at' => Carbon::now(),
        ]));
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, float|int|string>
     */
    private function statisticRow(array $row): array
    {
        unset($row['__key']);

        return $row;
    }
}
