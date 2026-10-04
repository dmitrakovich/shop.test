<?php

namespace App\Filament\Pages\Settings;

use App\Enums\Config\ConfigKey;
use App\Enums\Filament\NavGroup;
use App\Filament\Pages\Settings\Concerns\ManagesConfigForm;
use Filament\Forms\Components\Toggle;
use Filament\Pages\Page;
use Filament\Schemas\Schema;

class AutoOrderStatusesSettings extends Page
{
    use ManagesConfigForm;

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::Settings;

    protected static ?string $navigationLabel = 'Автоматические статусы заказа';

    protected static ?string $title = 'Автоматические статусы заказа';

    protected static ?string $slug = 'settings/auto-order-statuses';

    protected static ?int $navigationSort = 8;

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
                Toggle::make('active')
                    ->label('Включено'),
                Toggle::make('belpost_parse_email')
                    ->label('Email автопарсинг (БелПочта)'),
            ]);
    }
}
