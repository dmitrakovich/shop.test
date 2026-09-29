<?php

namespace App\Services\Order;

use App\Enums\Config\ConfigKey;
use App\Models\Config;
use App\Models\WorkSchedule;
use App\Services\AdministratorService;
use Illuminate\Support\Carbon;

class WorkScheduleCalendar
{
    public function __construct(private AdministratorService $administrators) {}

    /**
     * @return array{
     *     label: string,
     *     days: list<string>,
     *     rows: list<array{admin_user_id: int, name: string}>,
     *     shifts: array<string, array<string, bool>>
     * }
     */
    public function month(string $month): array
    {
        $start = Carbon::parse($month . '-01')->startOfMonth();
        $end = $start->copy()->endOfMonth();
        $schedule = Config::findCacheable(ConfigKey::DistribOrderSetup)['schedule'] ?? [];
        $admins = $this->administrators->getAdministratorList();

        $saved = WorkSchedule::query()
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get();

        $days = [];
        for ($day = $start->copy(); $day->lte($end); $day->addDay()) {
            $days[] = $day->toDateString();
        }

        $rows = [];
        $shifts = [];

        foreach ($schedule as $item) {
            $adminId = (int)$item['admin_user_id'];
            $rows[] = [
                'admin_user_id' => $adminId,
                'name' => (string)($admins->get($adminId) ?? $adminId),
            ];

            foreach ($days as $date) {
                $shifts[$date][(string)$adminId] = $saved->contains(
                    fn (WorkSchedule $row): bool => (int)$row->admin_user_id === $adminId
                        && $row->date?->toDateString() === $date,
                );
            }
        }

        return [
            'label' => $start->locale('ru')->translatedFormat('F Y'),
            'days' => $days,
            'rows' => $rows,
            'shifts' => $shifts,
        ];
    }

    /**
     * @param  array<string, array<int|string, mixed>>  $shifts
     */
    public function save(array $shifts): void
    {
        foreach ($shifts as $date => $admins) {
            $workingIds = [];

            foreach ($admins as $adminId => $checked) {
                if ($checked === true || $checked === 1 || $checked === '1') {
                    $workingIds[] = (int)$adminId;
                }
            }

            $query = WorkSchedule::query()->whereDate('date', $date);

            if ($workingIds === []) {
                $query->delete();
            } else {
                $query->whereNotIn('admin_user_id', $workingIds)->delete();
            }

            foreach ($workingIds as $adminId) {
                WorkSchedule::query()->updateOrCreate([
                    'admin_user_id' => $adminId,
                    'date' => $date,
                ]);
            }
        }
    }
}
