<?php

namespace App\Filament\Resources\Ads\Banners\Schemas;

use App\Enums\Ads\BannerPosition;
use App\Enums\Ads\BannerType;
use App\Models\Ads\Banner;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class BannerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->schema([
                        Section::make('Баннеры')
                            ->schema([
                                Fieldset::make('Десктоп')
                                    ->columns(1)
                                    ->schema(self::bannerBlock('desktop', 'десктоп')),
                                Fieldset::make('Мобильная версия')
                                    ->columns(1)
                                    ->schema(self::bannerBlock('mobile', 'мобильный')),
                            ]),

                    ])
                    ->columnSpan(['lg' => 2]),
                Group::make()
                    ->schema([
                        Section::make('Конфигурация')
                            ->schema([
                                Select::make('position')
                                    ->label('Позиция')
                                    ->options(BannerPosition::class)
                                    ->default(BannerPosition::INDEX_MAIN)
                                    ->native(false)
                                    ->required()
                                    ->disabled()
                                    ->dehydrated(),
                                TextInput::make('title')
                                    ->required()
                                    ->label('Заголовок')
                                    ->default('Акция!'),
                                TextInput::make('url')
                                    ->required()
                                    ->label('Ссылка'),
                                Toggle::make('active')
                                    ->label('Активный')
                                    ->default(true),
                                DateTimePicker::make('start_datetime')
                                    ->label('Дата начала')
                                    ->default(now()->addDay()->startOfDay())
                                    ->native(false),
                                DateTimePicker::make('end_datetime')
                                    ->label('Дата окончания')
                                    ->native(false),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    /**
     * Блок загрузки баннера (desktop / mobile)
     *
     * @return array<array-key, \Filament\Forms\Components\Field>
     */
    private static function bannerBlock(string $prefix, string $label): array
    {
        return [
            ToggleButtons::make("{$prefix}_type")
                ->label("Тип ({$label})")
                ->options(BannerType::class)
                ->inline()
                ->required()
                ->live(),

            SpatieMediaLibraryFileUpload::make("{$prefix}_image")
                ->label("Фото ({$label})")
                ->collection("{$prefix}_image")
                ->image()
                ->visible(fn (Get $get) => $get("{$prefix}_type")->isImage())
                ->required(fn (Get $get) => $get("{$prefix}_type")->isImage()),

            SpatieMediaLibraryFileUpload::make("{$prefix}_video")
                ->label("Видео ({$label})")
                ->collection("{$prefix}_video")
                ->acceptedFileTypes(Banner::ACCEPTED_VIDEO_TYPES)
                ->visible(fn (Get $get) => $get("{$prefix}_type")->isVideo())
                ->required(fn (Get $get) => $get("{$prefix}_type")->isVideo()),

            SpatieMediaLibraryFileUpload::make("{$prefix}_video_preview")
                ->label("Превью ({$label})")
                ->collection("{$prefix}_video_preview")
                ->image()
                ->visible(fn (Get $get) => $get("{$prefix}_type")->isVideo())
                ->required(fn (Get $get) => $get("{$prefix}_type")->isVideo()),
        ];
    }
}
