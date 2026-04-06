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
                                ToggleButtons::make('type')
                                    ->label('Тип баннера')
                                    ->options(BannerType::class)
                                    ->inline()
                                    ->required()
                                    ->live(),

                                Fieldset::make('Десктоп')
                                    ->columns(1)
                                    ->schema([
                                        SpatieMediaLibraryFileUpload::make('desktop_image')
                                            ->disk('media')
                                            ->label('Фото (десктоп)')
                                            ->collection('desktop_image')
                                            ->image()
                                            ->visible(fn (Get $get) => $get('type')->isImage())
                                            ->required(fn (Get $get) => $get('type')->isImage()),

                                        SpatieMediaLibraryFileUpload::make('desktop_video')
                                            ->disk('media')
                                            ->label('Видео (десктоп)')
                                            ->collection('desktop_video')
                                            ->acceptedFileTypes(Banner::ACCEPTED_VIDEO_TYPES)
                                            ->visible(fn (Get $get) => $get('type')->isVideo())
                                            ->required(fn (Get $get) => $get('type')->isVideo()),

                                        SpatieMediaLibraryFileUpload::make('desktop_video_preview')
                                            ->disk('media')
                                            ->label('Превью (десктоп)')
                                            ->collection('desktop_video_preview')
                                            ->image()
                                            ->visible(fn (Get $get) => $get('type')->isVideo())
                                            ->required(fn (Get $get) => $get('type')->isVideo()),
                                    ]),

                                Fieldset::make('Мобильная версия')
                                    ->columns(1)
                                    ->schema([
                                        SpatieMediaLibraryFileUpload::make('mobile_image')
                                            ->disk('media')
                                            ->label('Фото (мобильный)')
                                            ->collection('mobile_image')
                                            ->image()
                                            ->visible(fn (Get $get) => $get('type')->isImage()),

                                        SpatieMediaLibraryFileUpload::make('mobile_video')
                                            ->disk('media')
                                            ->label('Видео (мобильный)')
                                            ->collection('mobile_video')
                                            ->acceptedFileTypes(['video/mp4', 'video/webm'])
                                            ->visible(fn (Get $get) => $get('type')->isVideo()),

                                        SpatieMediaLibraryFileUpload::make('mobile_video_preview')
                                            ->disk('media')
                                            ->label('Превью (мобильный)')
                                            ->collection('mobile_video_preview')
                                            ->image()
                                            ->visible(fn (Get $get) => $get('type')->isVideo()),
                                    ]),
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
                                    ->default(BannerPosition::INDEX_TOP)
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
}
