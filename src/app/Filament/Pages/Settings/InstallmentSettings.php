<?php

namespace App\Filament\Pages\Settings;

use App\Enums\Filament\NavGroup;
use App\Filament\Pages\Settings\Concerns\ManagesConfigForm;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Schemas\Schema;

class InstallmentSettings extends Page
{
    use ManagesConfigForm;

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::Settings;

    protected static ?string $navigationLabel = 'Рассрочка';

    protected static ?string $title = 'Рассрочка';

    protected static ?string $slug = 'settings/installment';

    protected static ?int $navigationSort = 1;

    protected static function configKey(): string
    {
        return 'installment';
    }

    protected function getSavedNotificationTitle(): string
    {
        return 'Настройки рассрочки сохранены';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('min_price')
                    ->label('Минимальная сумма рассрочки')
                    ->numeric()
                    ->prefix('BYN')
                    ->required(),
                TextInput::make('min_price_3_parts')
                    ->label('Минимальная сумма на 3 платежа')
                    ->numeric()
                    ->prefix('BYN')
                    ->required(),
            ]);
    }
}
