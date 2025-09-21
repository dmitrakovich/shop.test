<?php

namespace App\Filament\Resources\Management\AdminUsers;

use App\Enums\Filament\NavGroup;
use App\Filament\Resources\Management\AdminUsers\Pages\CreateAdminUser;
use App\Filament\Resources\Management\AdminUsers\Pages\EditAdminUser;
use App\Filament\Resources\Management\AdminUsers\Pages\ListAdminUsers;
use App\Models\Admin\AdminUser;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserResource extends Resource
{
    protected static ?string $model = AdminUser::class;

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::MANAGEMENT;

    protected static ?string $modelLabel = 'Пользователь';

    protected static ?string $pluralModelLabel = 'Пользователи';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('username')
                    ->label('Логин')
                    ->required()
                    ->maxLength(190),
                TextInput::make('password')
                    ->label('Пароль')
                    ->password()
                    ->afterStateHydrated(function (TextInput $component) {
                        $component->state(null);
                    })
                    ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->required(fn (string $context): bool => $context === 'create')
                    ->maxLength(60),
                TextInput::make('name')
                    ->label('Имя')
                    ->required()
                    ->maxLength(191),
                TextInput::make('user_last_name')
                    ->label('Фамилия')
                    ->maxLength(64),
                TextInput::make('user_patronymic_name')
                    ->label('Отчество')
                    ->maxLength(32),
                TextInput::make('trust_number')
                    ->label('Номер доверенности')
                    ->maxLength(128),
                DatePicker::make('trust_date')
                    ->label('Дата доверенности')
                    ->native(false),
                Select::make('roles')
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
                TextColumn::make('username')
                    ->label('Логин')
                    ->searchable(),
                TextColumn::make('full_name')
                    ->label('ФИО')
                    ->getStateUsing(fn (AdminUser $adminUser) => $adminUser->getFullName())
                    ->searchable(),
                TextColumn::make('roles.name')
                    ->label('Роли')
                    ->badge(),
                TextColumn::make('trust_number')
                    ->label('Номер доверенности'),
                // Tables\Columns\TextColumn::make('trust_date')
                //     ->date()
                //     ->sortable(),
                TextColumn::make('created_at')
                    ->label('Дата создания')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Дата обновления')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->hidden(fn (AdminUser $user) => $user->id === auth()->id()),
                DeleteAction::make()
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
            'index' => ListAdminUsers::route('/'),
            'create' => CreateAdminUser::route('/create'),
            'edit' => EditAdminUser::route('/{record}/edit'),
        ];
    }
}
