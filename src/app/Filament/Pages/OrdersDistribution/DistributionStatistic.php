<?php

namespace App\Filament\Pages\OrdersDistribution;

use App\Enums\Filament\NavGroup;
use App\Exports\OrdersDistributionStatisticExport;
use App\Services\Order\OrdersDistributionStatistic;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DistributionStatistic extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::OrdersDistribution;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $navigationLabel = 'Статистика';

    protected static ?string $title = 'Статистика распределения';

    protected static ?string $slug = 'orders-distribution/statistic';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.orders-distribution.statistic';

    public function table(Table $table): Table
    {
        return $table
            ->records(function (?array $filters): array {
                return $this->rows($filters ?? [])->all();
            })
            ->columns([
                TextColumn::make('instance_name')
                    ->label('Менеджер'),
                TextColumn::make('distribution_count')
                    ->label('Заказов распределено')
                    ->numeric(),
                TextColumn::make('distribution_percentage')
                    ->label('% от всех распределенных')
                    ->numeric(decimalPlaces: 2),
                TextColumn::make('created_manually')
                    ->label('Создано вручную')
                    ->numeric(),
                TextColumn::make('accepted_manually')
                    ->label('Принято вручную')
                    ->numeric(),
                TextColumn::make('total_count')
                    ->label('Всего заказов (по дате создания)')
                    ->numeric(),
            ])
            ->filters([
                Filter::make('period')
                    ->schema([
                        DateTimePicker::make('start')
                            ->label('Начальная дата')
                            ->default(fn (): Carbon => now()->startOfDay()),
                        DateTimePicker::make('end')
                            ->label('Конечная дата')
                            ->default(fn (): Carbon => now()->endOfDay()),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->indicateUsing(function (array $state): array {
                        $indicators = [];

                        if (filled($state['start'] ?? null)) {
                            $indicators[] = Indicator::make(
                                'С ' . Carbon::parse($state['start'])->format('d.m.Y H:i'),
                            )->removeField('start');
                        }

                        if (filled($state['end'] ?? null)) {
                            $indicators[] = Indicator::make(
                                'По ' . Carbon::parse($state['end'])->format('d.m.Y H:i'),
                            )->removeField('end');
                        }

                        return $indicators;
                    }),
            ], layout: FiltersLayout::AboveContent)
            ->headerActions([
                Action::make('export')
                    ->label('Excel')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->action(fn (): BinaryFileResponse => $this->export()),
            ])
            ->columnManager(false)
            ->selectable(false)
            ->defaultSort('instance_name')
            ->paginated(false)
            ->striped();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, array{
     *     __key: string,
     *     instance_name: string,
     *     distribution_count: int,
     *     distribution_percentage: float,
     *     created_manually: int,
     *     accepted_manually: int,
     *     total_count: int
     * }>
     */
    private function rows(array $filters): Collection
    {
        [$start, $end] = $this->period($filters);

        return app(OrdersDistributionStatistic::class)->rows($start, $end);
    }

    private function export(): BinaryFileResponse
    {
        return Excel::download(
            new OrdersDistributionStatisticExport($this->rows($this->getTableFiltersForm()->getState())),
            'Статистика распределения.xlsx',
        );
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{0: Carbon, 1: Carbon}
     */
    private function period(array $filters): array
    {
        $period = is_array($filters['period'] ?? null) ? $filters['period'] : [];

        $start = filled($period['start'] ?? null)
            ? Carbon::parse($period['start'])
            : now()->startOfDay();
        $end = filled($period['end'] ?? null)
            ? Carbon::parse($period['end'])
            : now()->endOfDay();

        return [$start, $end];
    }
}
