<?php

namespace App\Filament\Pages\Settings;

use App\Enums\Config\ConfigKey;
use App\Enums\Filament\NavGroup;
use App\Filament\Pages\Settings\Concerns\ManagesConfigForm;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\Page;
use Filament\Schemas\Schema;

class SendingTracksSettings extends Page
{
    use ManagesConfigForm;

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::Settings;

    protected static ?string $navigationLabel = 'Отправка треков';

    protected static ?string $title = 'Отправка треков';

    protected static ?string $slug = 'settings/sending-tracks';

    protected static ?int $navigationSort = 9;

    protected static function configKey(): ConfigKey
    {
        return ConfigKey::SendingTracks;
    }

    protected function getSavedNotificationTitle(): string
    {
        return 'Настройки отправки треков сохранены';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Toggle::make('active')
                    ->label('Включена'),
                TagsInput::make('ignore_cities')
                    ->label('Города исключения')
                    ->trim()
                    ->splitKeys(['Tab', ','])
                    ->dehydrateStateUsing(fn (mixed $state): array => $this->normalizeCities($state)),
            ]);
    }

    /**
     * @return list<string>
     */
    private function normalizeCities(mixed $state): array
    {
        $cities = [];

        foreach ((array)$state as $city) {
            $city = mb_strtolower(trim((string)$city));

            if ($city === '') {
                continue;
            }

            $cities[] = $city;
        }

        return $cities;
    }
}
