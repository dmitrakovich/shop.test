<?php

namespace App\Filament\Pages\Settings;

use App\Enums\Config\ConfigKey;
use App\Enums\Filament\NavGroup;
use App\Filament\Pages\Settings\Concerns\ManagesConfigForm;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\Page;
use Filament\Schemas\Schema;

class NewsletterSettings extends Page
{
    use ManagesConfigForm;

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::Settings;

    protected static ?string $navigationLabel = 'Рассылка для зарегистрированных';

    protected static ?string $title = 'Рассылка для зарегистрированных';

    protected static ?string $slug = 'settings/newsletter';

    protected static ?int $navigationSort = 10;

    protected static function configKey(): ConfigKey
    {
        return ConfigKey::NewsletterRegister;
    }

    protected function getSavedNotificationTitle(): string
    {
        return 'Настройки рассылки сохранены';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Toggle::make('active')
                    ->label('Включена'),
                TextInput::make('to_days')
                    ->label('Количество дней после регистрации до')
                    ->numeric()
                    ->minValue(1)
                    ->required()
                    ->dehydrateStateUsing(fn (mixed $state): int => (int)$state),
                TextInput::make('from_days')
                    ->label('Количество дней после регистрации от')
                    ->numeric()
                    ->minValue(0)
                    ->required()
                    ->dehydrateStateUsing(fn (mixed $state): int => (int)$state),
            ]);
    }
}
