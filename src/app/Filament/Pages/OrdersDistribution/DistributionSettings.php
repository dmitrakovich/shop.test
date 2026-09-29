<?php

namespace App\Filament\Pages\OrdersDistribution;

use App\Enums\Config\ConfigKey;
use App\Enums\Filament\NavGroup;
use App\Filament\Pages\Settings\Concerns\ManagesConfigForm;
use App\Services\AdministratorService;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Pages\Page;
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
                            ->columns(2)
                            ->columnSpanFull()
                            ->schema([
                                Select::make('admin_user_id')
                                    ->label('Менеджер')
                                    ->options(fn (): array => app(AdministratorService::class)->getAdministratorList()->all())
                                    ->searchable()
                                    ->native(false)
                                    ->required()
                                    ->columnSpanFull()
                                    ->dehydrateStateUsing(fn (mixed $state): int => (int)$state),
                                TimePicker::make('time_from_even')
                                    ->label('С (четные дни)')
                                    ->seconds(false)
                                    ->native(false)
                                    ->required(),
                                TimePicker::make('time_to_even')
                                    ->label('До (четные дни)')
                                    ->seconds(false)
                                    ->native(false)
                                    ->required(),
                                TimePicker::make('time_from_odd')
                                    ->label('С (нечетные дни)')
                                    ->seconds(false)
                                    ->native(false)
                                    ->required(),
                                TimePicker::make('time_to_odd')
                                    ->label('До (нечетные дни)')
                                    ->seconds(false)
                                    ->native(false)
                                    ->required(),
                            ]),
                    ]),
            ]);
    }
}
