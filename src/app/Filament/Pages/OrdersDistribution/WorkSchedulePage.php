<?php

namespace App\Filament\Pages\OrdersDistribution;

use App\Enums\Filament\NavGroup;
use App\Services\Order\WorkScheduleCalendar;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;

class WorkSchedulePage extends Page
{
    protected static string|\UnitEnum|null $navigationGroup = NavGroup::OrdersDistribution;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $navigationLabel = 'График работы';

    protected static ?string $title = 'График работы';

    protected static ?string $slug = 'orders-distribution/work-schedule';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.orders-distribution.work-schedule';

    public string $month = '';

    public string $monthLabel = '';

    /**
     * @var list<string>
     */
    public array $days = [];

    /**
     * @var list<array{admin_user_id: int, name: string}>
     */
    public array $rows = [];

    /**
     * @var array<string, array<string, bool>>
     */
    public array $shifts = [];

    public function mount(WorkScheduleCalendar $calendar): void
    {
        $this->month = now()->format('Y-m');
        $this->fillMonth($calendar);
    }

    public function previousMonth(WorkScheduleCalendar $calendar): void
    {
        $this->month = Carbon::parse($this->month . '-01')->subMonth()->format('Y-m');
        $this->fillMonth($calendar);
    }

    public function nextMonth(WorkScheduleCalendar $calendar): void
    {
        $this->month = Carbon::parse($this->month . '-01')->addMonth()->format('Y-m');
        $this->fillMonth($calendar);
    }

    public function save(WorkScheduleCalendar $calendar): void
    {
        $calendar->save($this->shifts);

        Notification::make()
            ->title('График работы сохранён')
            ->success()
            ->send();
    }

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('previousMonth')
                ->label('Предыдущий')
                ->icon(Heroicon::OutlinedChevronLeft)
                ->color('gray')
                ->action(fn (WorkScheduleCalendar $calendar) => $this->previousMonth($calendar)),
            Action::make('nextMonth')
                ->label('Следующий')
                ->icon(Heroicon::OutlinedChevronRight)
                ->color('gray')
                ->action(fn (WorkScheduleCalendar $calendar) => $this->nextMonth($calendar)),
            Action::make('save')
                ->label('Сохранить')
                ->action(fn (WorkScheduleCalendar $calendar) => $this->save($calendar)),
        ];
    }

    private function fillMonth(WorkScheduleCalendar $calendar): void
    {
        $month = $calendar->month($this->month);
        $this->monthLabel = $month['label'];
        $this->days = $month['days'];
        $this->rows = $month['rows'];
        $this->shifts = $month['shifts'];
    }
}
