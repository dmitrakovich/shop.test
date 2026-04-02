<?php

namespace App\Filament\Resources\Ads\Banners\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BannerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('position')
                    ->options([
            'catalog_top' => 'Catalog top',
            'index_main' => 'Index main',
            'index_top' => 'Index top',
            'index_bottom' => 'Index bottom',
            'main_menu_catalog' => 'Main menu catalog',
            'catalog_mob' => 'Catalog mob',
            'feedback' => 'Feedback',
            'feedback_mob' => 'Feedback mob',
        ]),
                TextInput::make('title'),
                TextInput::make('url')
                    ->url(),
                TextInput::make('priority')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('active')
                    ->required(),
                DateTimePicker::make('start_datetime'),
                DateTimePicker::make('end_datetime'),
                Toggle::make('show_timer'),
                TextInput::make('spoiler'),
            ]);
    }
}
