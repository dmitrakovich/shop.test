<?php

namespace App\Filament\Pages\Settings;

use App\Enums\Config\ConfigKey;
use App\Enums\Feedback\ReviewDiscountType;
use App\Enums\Filament\NavGroup;
use App\Filament\Pages\Settings\Concerns\ManagesConfigForm;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FeedbackSettings extends Page
{
    use ManagesConfigForm;

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::Settings;

    protected static ?string $navigationLabel = 'Отзывы';

    protected static ?string $title = 'Отзывы';

    protected static ?string $slug = 'settings/feedback';

    protected static ?int $navigationSort = 2;

    protected static function configKey(): ConfigKey
    {
        return ConfigKey::Feedback;
    }

    protected function getSavedNotificationTitle(): string
    {
        return 'Настройки отзывов сохранены';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                ...array_map(
                    fn (ReviewDiscountType $type) => Section::make($type->getLabel())
                        ->schema([
                            TextInput::make("discount.{$type->value}.BYN")
                                ->label('BYN')
                                ->numeric()
                                ->required(),
                            TextInput::make("discount.{$type->value}.USD")
                                ->label('USD')
                                ->numeric()
                                ->required(),
                            TextInput::make("discount.{$type->value}.KZT")
                                ->label('KZT')
                                ->numeric()
                                ->required(),
                            TextInput::make("discount.{$type->value}.RUB")
                                ->label('RUB')
                                ->numeric()
                                ->required(),
                        ])
                        ->columns(4),
                    ReviewDiscountType::cases(),
                ),
                TextInput::make('send_after')
                    ->label('Отправлять смс через (часов)')
                    ->numeric()
                    ->required(),
            ]);
    }
}
