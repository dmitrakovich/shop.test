<?php

namespace App\Filament\Resources\Products\RatingAlgorithms\Schemas;

use App\Enums\Product\RatingFactor;
use App\Filament\Resources\Products\RatingAlgorithms\Support\RatingAlgorithmSelects;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class RatingAlgorithmForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Основное')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->label('Название алгоритма')
                            ->required()
                            ->maxLength(255),
                    ]),
                Section::make('Коэффициенты')
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema(self::coefficientInputs()),
                Section::make('Повышение')
                    ->description('Для выбранных категорий и товаров score +100')
                    ->icon(Heroicon::OutlinedArrowTrendingUp)
                    ->iconColor('success')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        RatingAlgorithmSelects::categoryUp(),
                        RatingAlgorithmSelects::productUp(),
                    ]),
                Section::make('Понижение')
                    ->description('Для выбранных категорий и товаров score -100')
                    ->icon(Heroicon::OutlinedArrowTrendingDown)
                    ->iconColor('danger')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        RatingAlgorithmSelects::categoryDown(),
                        RatingAlgorithmSelects::productDown(),
                    ]),
            ]);
    }

    /**
     * @return list<TextInput>
     */
    private static function coefficientInputs(): array
    {
        return array_map(
            fn (RatingFactor $factor): TextInput => self::coefficientInput($factor),
            RatingFactor::cases()
        );
    }

    private static function coefficientInput(RatingFactor $factor): TextInput
    {
        $input = TextInput::make($factor->coefficientColumn())
            ->label($factor->getLabel())
            ->numeric()
            ->rules(['integer'])
            ->default(0)
            ->required();

        $prefix = $factor->boostPenaltyPrefix();

        return $prefix !== null ? $input->prefix($prefix) : $input;
    }
}
