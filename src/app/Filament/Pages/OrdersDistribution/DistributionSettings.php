<?php

namespace App\Filament\Pages\OrdersDistribution;

use App\Enums\Config\ConfigKey;
use App\Enums\Filament\NavGroup;
use App\Filament\Pages\Settings\Concerns\ManagesConfigForm;
use App\Services\AdministratorService;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class DistributionSettings extends Page
{
    use ManagesConfigForm;

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::OrdersDistribution;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static ?string $navigationLabel = 'Настройки';

    protected static ?string $title = 'Настройки распределения';

    protected static ?string $slug = 'orders-distribution/settings';

    protected static ?int $navigationSort = 1;

    protected static function configKey(): ConfigKey
    {
        return ConfigKey::DistribOrderSetup;
    }

    protected function getSavedNotificationTitle(): string
    {
        return 'Настройки распределения сохранены';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Распределение')
                    ->schema([
                        Toggle::make('active')
                            ->label('Распределение заказов включено'),
                        Repeater::make('schedule')
                            ->label('Расписание')
                            ->addActionLabel('Добавить менеджера')
                            ->defaultItems(0)
                            ->reorderable(false)
                            ->compact()
                            ->columnSpanFull()
                            ->table([
                                TableColumn::make('Менеджер')->markAsRequired()->width('22%'),
                                TableColumn::make('Время работы (четные дни)')->markAsRequired(),
                                TableColumn::make('Время работы (нечетные дни)')->markAsRequired(),
                            ])
                            ->schema([
                                Select::make('admin_user_id')
                                    ->hiddenLabel()
                                    ->options(fn (): array => app(AdministratorService::class)->getAdministratorList()->all())
                                    ->searchable()
                                    ->native(false)
                                    ->required()
                                    ->dehydrateStateUsing(fn (mixed $state): int => (int)$state),
                                $this->timeRange('time_from_even', 'time_to_even'),
                                $this->timeRange('time_from_odd', 'time_to_odd'),
                            ]),
                    ]),
            ]);
    }

    private function timeRange(string $from, string $to): Grid
    {
        return Grid::make(2)->schema([
            TimePicker::make($from)
                ->hiddenLabel()
                ->prefix('с')
                ->seconds(false)
                ->native(false)
                ->required(),
            TimePicker::make($to)
                ->hiddenLabel()
                ->prefix('до')
                ->seconds(false)
                ->native(false)
                ->required(),
        ]);
    }
}
