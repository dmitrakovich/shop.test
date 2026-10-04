<?php

namespace App\Filament\Pages\Settings;

use App\Enums\Config\ConfigKey;
use App\Filament\Pages\Settings\Concerns\ConfigClusterPage;
use App\Filament\Pages\Settings\Concerns\ManagesConfigForm;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class SendingTracksSettings extends Page
{
    use ConfigClusterPage;
    use ManagesConfigForm;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedPaperAirplane;

    protected static ?string $navigationLabel = 'Отправка треков';

    protected static ?string $title = 'Отправка треков';

    protected ?string $subheading = 'SMS с трек-номером после отправки заказа';

    protected static ?string $slug = 'sending-tracks';

    protected static ?int $navigationSort = 4;

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
                Section::make()
                    ->schema([
                        Toggle::make('active')
                            ->label('Включена')
                            ->helperText('Отправляет SMS с треком по заказам в статусах «Отправлен» и «Примерка».')
                            ->onColor('success'),
                        TagsInput::make('ignore_cities')
                            ->label('Города исключения')
                            ->helperText('В эти города SMS не уходит. Регистр не важен.')
                            ->placeholder('Добавить город')
                            ->trim()
                            ->splitKeys(['Tab', ','])
                            ->dehydrateStateUsing(fn (mixed $state): array => $this->normalizeCities($state)),
                    ]),
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
