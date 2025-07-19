<?php

namespace App\Filament\Resources\User;

use App\Enums\User\BanReason;
use App\Filament\Resources\User\DeviceResource\Pages;
use App\Models\User\Device;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DeviceResource extends Resource
{
    protected static ?string $model = Device::class;

    protected static ?string $navigationGroup = 'user';

    protected static ?string $modelLabel = 'Устройство';

    protected static ?string $pluralModelLabel = 'Устройства';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('web_id')
                    ->maxLength(32),
                Forms\Components\TextInput::make('api_id')
                    ->maxLength(36),
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'id'),
                Forms\Components\TextInput::make('yandex_id')
                    ->numeric(),
                Forms\Components\TextInput::make('google_id')
                    ->maxLength(32),
                Forms\Components\TextInput::make('type')
                    ->required(),
                Forms\Components\TextInput::make('ip_address')
                    ->maxLength(45),
                Forms\Components\TextInput::make('country_code')
                    ->maxLength(2),
                Forms\Components\DateTimePicker::make('banned_at'),
                Forms\Components\TextInput::make('ban_reason')
                    ->numeric(),
                Forms\Components\TextInput::make('agent')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('is_web')
                    ->getStateUsing(fn (Device $record) => isset($record->web_id))
                    ->label('Web')
                    ->alignCenter()
                    ->boolean(),
                Tables\Columns\TextColumn::make('web_id')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_api')
                    ->getStateUsing(fn (Device $record) => isset($record->api_id))
                    ->label('API')
                    ->alignCenter()
                    ->boolean(),
                Tables\Columns\TextColumn::make('api_id')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('user.id')
                    ->label('Пользователь')
                    ->url(fn (?int $state) => $state
                        ? UserResource::getUrl('edit', ['record' => $state])
                        : null
                    )
                    ->openUrlInNewTab()
                    ->formatStateUsing(fn ($state) => "ID: $state")
                    ->icon('heroicon-o-user')
                    ->color('primary'),
                Tables\Columns\TextColumn::make('yandex_id')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('google_id')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('type')
                    ->label('Устройство')
                    ->tooltip(fn (string $state): string => $state === 'mobile'
                    ? 'Мобильное устройство'
                    : 'Настольный компьютер')
                    ->icon(fn (string $state): string => match ($state) {
                        'mobile' => 'heroicon-o-device-phone-mobile',
                        'desktop' => 'heroicon-o-computer-desktop',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'mobile' => 'info',
                        'desktop' => 'primary',
                    })
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP'),
                Tables\Columns\TextColumn::make('country_code')
                    ->label('Страна')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('banned_at')
                    ->label('Дата блокировки')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ban_reason')
                    ->label('Причина блокировки')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('agent'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->defaultSort('id', 'desc')
            ->actions([
                // Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('toggleBan')
                    ->label(fn (Device $record) => $record->isBanned() ? 'Разбанить' : 'Забанить')
                    ->icon(fn (Device $record) => $record->isBanned() ? 'heroicon-o-lock-open' : 'heroicon-o-lock-closed')
                    ->color(fn (Device $record) => $record->isBanned() ? 'success' : 'danger')
                    ->requiresConfirmation()
                    ->action(function (Device $record) {
                        $record->isBanned() ? $record->unban() : $record->ban(BanReason::BY_ADMIN);
                    }),
            ])
            ->recordClasses(
                fn (Device $record) => $record->isBanned() ? 'bg-danger/10' : null
            );
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDevices::route('/'),
        ];
    }
}
