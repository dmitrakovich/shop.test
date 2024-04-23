<?php

namespace App\Filament\Resources\Management;

use App\Filament\Resources\Management\AdminUserResource\Pages;
use App\Models\Admin\AdminUser;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserResource extends Resource
{
    protected static ?string $model = AdminUser::class;

    protected static ?string $navigationGroup = 'management';

    protected static ?string $modelLabel = 'Пользователь';

    protected static ?string $pluralModelLabel = 'Пользователи';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('username')
                    ->label('Логин')
                    ->required()
                    ->maxLength(190),
                Forms\Components\TextInput::make('password')
                    ->label('Пароль')
                    ->password()
                    ->afterStateHydrated(function (Forms\Components\TextInput $component) {
                        $component->state(null);
                    })
                    ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->required(fn (string $context): bool => $context === 'create')
                    ->maxLength(60),
                Forms\Components\TextInput::make('name')
                    ->label('Имя')
                    ->required()
                    ->maxLength(191),
                Forms\Components\TextInput::make('user_last_name')
                    ->label('Фамилия')
                    ->maxLength(64),
                Forms\Components\TextInput::make('user_patronymic_name')
                    ->label('Отчество')
                    ->maxLength(32),
                Forms\Components\TextInput::make('trust_number')
                    ->label('Номер доверенности')
                    ->maxLength(128),
                Forms\Components\DatePicker::make('trust_date')
                    ->label('Дата доверенности')
                    ->native(false),
                Forms\Components\Select::make('roles')
                    ->label('Роли')
                    ->multiple()
                    ->preload()
                    ->searchable(false)
                    ->relationship('roles')
                    ->options(Role::query()->pluck('name', 'id')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('username')
                    ->label('Логин')
                    ->searchable(),
                Tables\Columns\TextColumn::make('full_name')
                    ->label('ФИО')
                    ->getStateUsing(fn (AdminUser $adminUser) => $adminUser->getFullName())
                    ->searchable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Роли')
                    ->badge(),
                Tables\Columns\TextColumn::make('trust_number')
                    ->label('Номер доверенности'),
                // Tables\Columns\TextColumn::make('trust_date')
                //     ->date()
                //     ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата создания')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Дата обновления')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->hidden(fn (AdminUser $user) => $user->id === auth()->id()),
                Tables\Actions\DeleteAction::make()
                    ->hidden(fn (AdminUser $user) => $user->id === auth()->id()),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdminUsers::route('/'),
            'create' => Pages\CreateAdminUser::route('/create'),
            'edit' => Pages\EditAdminUser::route('/{record}/edit'),
        ];
    }
}
