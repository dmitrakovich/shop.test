<?php

namespace App\Filament\Pages\Settings;

use App\Enums\Config\ConfigKey;
use App\Enums\Feedback\ReviewDiscountType;
use App\Filament\Pages\Settings\Concerns\ConfigClusterPage;
use App\Filament\Pages\Settings\Concerns\ManagesConfigForm;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class FeedbackSettings extends Page
{
    use ConfigClusterPage;
    use ManagesConfigForm;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?string $navigationLabel = 'Отзывы';

    protected static ?string $title = 'Отзывы';

    protected ?string $subheading = 'Скидки за отзыв и SMS после завершения заказа';

    protected static ?string $slug = 'feedback';

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
                Section::make()
                    ->schema([
                        TextInput::make('send_after')
                            ->label('Отправлять смс через (часов)')
                            ->helperText('Через сколько часов после завершения заказа отправить просьбу оставить отзыв.')
                            ->numeric()
                            ->required(),
                    ]),
            ]);
    }
}
