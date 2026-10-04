<?php

namespace App\Filament\Pages\Settings;

use App\Enums\Config\ConfigKey;
use App\Filament\Pages\Settings\Concerns\ConfigClusterPage;
use App\Filament\Pages\Settings\Concerns\ManagesConfigForm;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class NewsletterSettings extends Page
{
    use ConfigClusterPage;
    use ManagesConfigForm;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?string $navigationLabel = 'Рассылка для зарегистрированных';

    protected static ?string $title = 'Рассылка для зарегистрированных';

    protected ?string $subheading = 'SMS-скидка пользователям без заказов после регистрации';

    protected static ?string $slug = 'newsletter';

    protected static ?int $navigationSort = 5;

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
                Section::make()
                    ->columns(2)
                    ->schema([
                        Toggle::make('active')
                            ->label('Включена')
                            ->helperText('Отправляет скидку пользователям без заказов в выбранном диапазоне дней.')
                            ->onColor('success')
                            ->columnSpanFull(),
                        TextInput::make('from_days')
                            ->label('Количество дней после регистрации от')
                            ->helperText('Минимальный возраст аккаунта.')
                            ->numeric()
                            ->minValue(0)
                            ->required()
                            ->dehydrateStateUsing(fn (mixed $state): int => (int)$state),
                        TextInput::make('to_days')
                            ->label('Количество дней после регистрации до')
                            ->helperText('Максимальный возраст аккаунта.')
                            ->numeric()
                            ->minValue(1)
                            ->required()
                            ->dehydrateStateUsing(fn (mixed $state): int => (int)$state),
                    ]),
            ]);
    }
}
