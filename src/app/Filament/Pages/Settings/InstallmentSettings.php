<?php

namespace App\Filament\Pages\Settings;

use App\Enums\Config\ConfigKey;
use App\Filament\Pages\Settings\Concerns\ConfigClusterPage;
use App\Filament\Pages\Settings\Concerns\ManagesConfigForm;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class InstallmentSettings extends Page
{
    use ConfigClusterPage;
    use ManagesConfigForm;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $navigationLabel = 'Рассрочка';

    protected static ?string $title = 'Рассрочка';

    protected ?string $subheading = 'Минимальные суммы для оплаты частями';

    protected static ?string $slug = 'installment';

    protected static ?int $navigationSort = 1;

    protected static function configKey(): ConfigKey
    {
        return ConfigKey::Installment;
    }

    protected function getSavedNotificationTitle(): string
    {
        return 'Настройки рассрочки сохранены';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->schema([
                        TextInput::make('min_price')
                            ->label('Минимальная сумма рассрочки')
                            ->helperText('Ниже этой суммы рассрочка не предлагается.')
                            ->numeric()
                            ->prefix('BYN')
                            ->required(),
                        TextInput::make('min_price_3_parts')
                            ->label('Минимальная сумма на 3 платежа')
                            ->helperText('Порог для схемы на три платежа.')
                            ->numeric()
                            ->prefix('BYN')
                            ->required(),
                    ]),
            ]);
    }
}
