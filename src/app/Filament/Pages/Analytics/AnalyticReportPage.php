<?php

namespace App\Filament\Pages\Analytics;

use App\Enums\Analytics\AnalyticReport;
use App\Enums\Filament\NavGroup;
use App\Exports\AnalyticsReportExport;
use App\Services\Analytics\AnalyticReportService;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

abstract class AnalyticReportPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::Analytics;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected string $view = 'filament.pages.analytics.report';

    abstract protected static function report(): AnalyticReport;

    public static function getNavigationLabel(): string
    {
        return static::report()->getLabel();
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return static::report()->getLabel();
    }

    public static function getNavigationSort(): ?int
    {
        return static::report()->navigationSort();
    }

    public static function getSlug(?\Filament\Panel $panel = null): string
    {
        return 'analytics/' . static::report()->value;
    }

    public function table(Table $table): Table
    {
        $report = static::report();

        return $table
            ->records(function (
                ?string $sortColumn,
                ?string $sortDirection,
                array $filters,
            ) use ($report): array {
                $rows = $this->reportRows($report, $filters);

                if (blank($sortColumn)) {
                    $sortColumn = 'total_purchased_price';
                    $sortDirection = 'desc';
                }

                $rows = $sortDirection === 'desc'
                    ? $rows->sortByDesc($sortColumn)
                    : $rows->sortBy($sortColumn);

                return $rows->all();
            })
            ->columns($this->tableColumns($report))
            ->defaultSort('total_purchased_price', 'desc')
            ->filters([
                Filter::make('period')
                    ->schema([
                        DatePicker::make('start')
                            ->label('Начальная дата')
                            ->default(fn (): ?Carbon => $report->hasDefaultDateFilter()
                                ? now()->subDays(8)->startOfDay()
                                : null),
                        DatePicker::make('end')
                            ->label('Конечная дата')
                            ->default(fn (): ?Carbon => $report->hasDefaultDateFilter()
                                ? now()->subDays(1)->endOfDay()
                                : null),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->indicateUsing(function (array $state): array {
                        $indicators = [];

                        if (filled($state['start'] ?? null)) {
                            $indicators[] = Indicator::make(
                                'С ' . Carbon::parse($state['start'])->format('d.m.Y'),
                            )->removeField('start');
                        }

                        if (filled($state['end'] ?? null)) {
                            $indicators[] = Indicator::make(
                                'По ' . Carbon::parse($state['end'])->format('d.m.Y'),
                            )->removeField('end');
                        }

                        return $indicators;
                    }),
            ])
            ->headerActions([
                Action::make('export')
                    ->label('Excel')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->action(fn (): BinaryFileResponse => $this->export()),
            ])
            ->paginated(false)
            ->striped();
    }

    /**
     * @return array<int, TextColumn>
     */
    private function tableColumns(AnalyticReport $report): array
    {
        $columns = [
            TextColumn::make('instance_name')
                ->label($report->dimensionLabel())
                ->wrap(),
        ];

        if ($report->showsUtmDetails()) {
            $columns[] = TextColumn::make('channel_name')
                ->label('Канал')
                ->placeholder('—');
            $columns[] = TextColumn::make('company_name')
                ->label('Компания')
                ->placeholder('—');
        }

        return [
            ...$columns,
            TextColumn::make('total_count')
                ->label('Все')
                ->numeric()
                ->sortable()
                ->summarize($this->sumSummarizer('total_count', 'Итого')),
            TextColumn::make('accepted_count')
                ->label('Принят')
                ->numeric()
                ->sortable()
                ->summarize($this->sumSummarizer('accepted_count')),
            TextColumn::make('in_progress_count')
                ->label('В работе')
                ->numeric()
                ->sortable()
                ->summarize($this->sumSummarizer('in_progress_count')),
            TextColumn::make('purchased_count')
                ->label('Выкуплен')
                ->numeric()
                ->sortable()
                ->summarize($this->sumSummarizer('purchased_count')),
            TextColumn::make('canceled_count')
                ->label('Отменен')
                ->numeric()
                ->sortable()
                ->summarize($this->sumSummarizer('canceled_count')),
            TextColumn::make('returned_count')
                ->label('Возврат')
                ->numeric()
                ->sortable()
                ->summarize($this->sumSummarizer('returned_count')),
            TextColumn::make('total_purchased_price')
                ->label('Сумма выкупленных')
                ->numeric(decimalPlaces: 2)
                ->suffix(' BYN')
                ->sortable()
                ->summarize($this->sumSummarizer('total_purchased_price', suffix: ' BYN', decimalPlaces: 2)),
            TextColumn::make('purchase_percentage')
                ->label('Процент выкупа')
                ->numeric(decimalPlaces: 2)
                ->suffix('%')
                ->sortable()
                ->summarize($this->averageSummarizer('purchase_percentage', suffix: '%')),
            TextColumn::make('total_lost_price')
                ->label('Сумма потерянных')
                ->numeric(decimalPlaces: 2)
                ->suffix(' BYN')
                ->sortable()
                ->summarize($this->sumSummarizer('total_lost_price', suffix: ' BYN', decimalPlaces: 2)),
        ];
    }

    private function sumSummarizer(
        string $attribute,
        ?string $label = '',
        ?string $suffix = null,
        ?int $decimalPlaces = null,
    ): Summarizer {
        $summarizer = Summarizer::make()
            ->label($label)
            ->using(function (Table $table) use ($attribute, $decimalPlaces): float|int {
                $total = collect($table->getRecords())->sum($attribute);

                return $decimalPlaces === null
                    ? (int)$total
                    : round((float)$total, $decimalPlaces);
            });

        if ($decimalPlaces !== null) {
            $summarizer->numeric(decimalPlaces: $decimalPlaces);
        }

        if ($suffix !== null) {
            $summarizer->suffix($suffix);
        }

        return $summarizer;
    }

    private function averageSummarizer(string $attribute, string $suffix): Summarizer
    {
        return Summarizer::make()
            ->label('')
            ->numeric(decimalPlaces: 2)
            ->suffix($suffix)
            ->using(function (Table $table) use ($attribute): float {
                $records = collect($table->getRecords());

                if ($records->isEmpty()) {
                    return 0.0;
                }

                return round((float)$records->avg($attribute), 2);
            });
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int|string, array<string, mixed>>
     */
    private function reportRows(AnalyticReport $report, array $filters): Collection
    {
        [$start, $end] = $this->dateRangeFromFilters($filters);

        return app(AnalyticReportService::class)->rows($report, $start, $end);
    }

    private function export(): BinaryFileResponse
    {
        $report = static::report();
        $filters = $this->getTableFiltersForm()->getState();
        $rows = $this->reportRows($report, $filters);

        return Excel::download(
            new AnalyticsReportExport($report, $rows),
            $report->getLabel() . '.xlsx',
        );
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{0: ?Carbon, 1: ?Carbon}
     */
    private function dateRangeFromFilters(array $filters): array
    {
        $period = $filters['period'] ?? [];
        $start = filled($period['start'] ?? null)
            ? Carbon::parse($period['start'])->startOfDay()
            : null;
        $end = filled($period['end'] ?? null)
            ? Carbon::parse($period['end'])->endOfDay()
            : null;

        return [$start, $end];
    }
}
