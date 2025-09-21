<?php

namespace App\Filament\Resources\User;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use App\Filament\Resources\User\DeviceResource\Pages\ListDevices;
use App\Enums\Filament\NavGroup;
use App\Filament\Actions\ToggleDeviceBanAction;
use App\Filament\Resources\User\DeviceResource\Pages;
use App\Models\User\Device;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DeviceResource extends Resource
{
    protected static ?string $model = Device::class;

    protected static string | \UnitEnum | null $navigationGroup = NavGroup::USER;

    protected static ?string $modelLabel = 'Устройство';

    protected static ?string $pluralModelLabel = 'Устройства';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('web_id')
                    ->maxLength(32),
                TextInput::make('api_id')
                    ->maxLength(36),
                Select::make('user_id')
                    ->relationship('user', 'id'),
                TextInput::make('yandex_id')
                    ->numeric(),
                TextInput::make('google_id')
                    ->maxLength(32),
                TextInput::make('type')
                    ->required(),
                TextInput::make('ip_address')
                    ->maxLength(45),
                TextInput::make('country_code')
                    ->maxLength(2),
                DateTimePicker::make('banned_at'),
                TextInput::make('ban_reason')
                    ->numeric(),
                TextInput::make('agent')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('is_web')
                    ->getStateUsing(fn (Device $record) => isset($record->web_id))
                    ->label('Web')
                    ->alignCenter()
                    ->boolean(),
                TextColumn::make('web_id')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_api')
                    ->getStateUsing(fn (Device $record) => isset($record->api_id))
                    ->label('API')
                    ->alignCenter()
                    ->boolean(),
                TextColumn::make('api_id')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('user.id')
                    ->label('Пользователь')
                    ->url(fn (?int $state) => $state
                        ? UserResource::getUrl('edit', ['record' => $state])
                        : null
                    )
                    ->openUrlInNewTab()
                    ->formatStateUsing(fn ($state) => "ID: $state")
                    ->icon('heroicon-o-user')
                    ->color('primary'),
                TextColumn::make('yandex_id')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('google_id')
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('type')
                    ->label('Устройство')
                    ->tooltip(fn (string $state): string => $state === 'mobile'
                        ? 'Мобильное устройство'
                        : 'Настольный компьютер'
                    )
                    ->icon(fn (string $state): string => $state === 'mobile'
                        ? 'heroicon-o-device-phone-mobile'
                        : 'heroicon-o-computer-desktop'
                    )
                    ->color(fn (string $state): string => $state === 'mobile' ? 'info' : 'primary')
                    ->alignCenter(),
                TextColumn::make('ip_address')
                    ->label('IP'),
                TextColumn::make('country_code')
                    ->label('Страна')
                    ->alignCenter(),
                TextColumn::make('banned_at')
                    ->label('Дата блокировки')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('ban_reason')
                    ->label('Причина блокировки')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('agent'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->defaultSort('id', 'desc')
            ->recordActions([
                // Tables\Actions\EditAction::make(),
                ToggleDeviceBanAction::make(),
            ])
            ->recordClasses(
                fn (Device $record) => $record->isBanned() ? 'bg-danger/10' : null
            );
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDevices::route('/'),
        ];
    }
}
