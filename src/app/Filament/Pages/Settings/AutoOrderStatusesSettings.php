<?php

namespace App\Filament\Pages\Settings;

use App\Enums\Config\ConfigKey;
use App\Filament\Pages\Settings\Concerns\ConfigClusterPage;
use App\Filament\Pages\Settings\Concerns\ManagesConfigForm;
use Filament\Forms\Components\Toggle;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class AutoOrderStatusesSettings extends Page
{
    use ConfigClusterPage;
    use ManagesConfigForm;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPath;

    protected static ?string $navigationLabel = 'Автоматические статусы заказа';

    protected static ?string $title = 'Автоматические статусы заказа';

    protected ?string $subheading = 'Автосмена статусов заказа и разбор писем Белпочты';

    protected static ?string $slug = 'auto-order-statuses';

    protected static ?int $navigationSort = 3;

    protected static function configKey(): ConfigKey
    {
        return ConfigKey::AutoOrderStatuses;
    }

    protected function getSavedNotificationTitle(): string
    {
        return 'Настройки автоматических статусов сохранены';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Toggle::make('active')
                            ->label('Включено')
                            ->helperText('Автоматически меняет статусы заказа по правилам системы.')
                            ->onColor('success'),
                        Toggle::make('belpost_parse_email')
                            ->label('Email автопарсинг (БелПочта)')
                            ->helperText('Разбирает письма Белпочты о наложенном платеже.')
                            ->onColor('success'),
                    ]),
            ]);
    }
}
