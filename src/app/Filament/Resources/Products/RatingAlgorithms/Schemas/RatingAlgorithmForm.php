<?php

namespace App\Filament\Resources\Products\RatingAlgorithms\Schemas;

use App\Enums\Product\RatingFactor;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

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
        return TextInput::make($factor->coefficientColumn())
            ->label($factor->getLabel())
            ->numeric()
            ->rules(['integer'])
            ->default(0)
            ->required();
    }
}
