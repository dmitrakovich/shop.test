<?php

namespace App\Filament\Resources\Users\DeviceConsents;

use App\Enums\Consent\ConsentFormEnum;
use App\Enums\Filament\NavGroup;
use App\Filament\Resources\Users\DeviceConsents\Pages\ListDeviceConsents;
use App\Models\User\DeviceConsent;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DeviceConsentResource extends Resource
{
    protected static ?string $model = DeviceConsent::class;

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::Users;

    protected static ?string $modelLabel = 'Согласие на обработку персональных данных';

    protected static ?string $pluralModelLabel = 'Согласия на обработку персональных данных';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('device_id')
                    ->label('Устройство')
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('user_id')
                    ->label('Пользователь')
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('fio')
                    ->label('ФИО')
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('device.api_id')
                    ->label('Device API ID')
                    ->searchable()
                    ->toggleable()
                    ->placeholder('—'),
                IconColumn::make('cookie_analytics_enabled')
                    ->label('Аналитика')
                    ->boolean()
                    ->alignCenter(),
                IconColumn::make('cookie_marketing_enabled')
                    ->label('Маркетинг')
                    ->boolean()
                    ->alignCenter(),
                IconColumn::make('personal_data_consent')
                    ->label('Согласие на обработку персональных данных')
                    ->boolean()
                    ->alignCenter(),
                TextColumn::make('consent_request_source')
                    ->label('Форма')
                    ->badge()
                    ->formatStateUsing(fn(?ConsentFormEnum $state): ?string => $state?->label())
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Создано')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('consent_request_source')
                    ->label('Форма')
                    ->options(collect(ConsentFormEnum::cases())->mapWithKeys(
                        fn(ConsentFormEnum $c): array => [$c->value => $c->label()]
                    )),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDeviceConsents::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['device', 'user']);
    }
}
