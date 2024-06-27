<?php

namespace App\Filament\Resources\User;

use App\Filament\Resources\User\UserResource\Pages;
use App\Models\User\Group;
use App\Models\User\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\SelectConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationGroup = 'user';

    protected static ?string $modelLabel = 'Пользователи';

    protected static ?string $pluralModelLabel = 'Пользователи';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Tabs')
                    ->tabs([
                        Tab::make('Основная информация')
                            ->schema([
                                TextInput::make('first_name')
                                    ->label('Имя')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('last_name')
                                    ->label('Фамилия')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('patronymic_name')
                                    ->label('Отчество')
                                    ->maxLength(255),
                                TextInput::make('email')
                                    ->label('E-mail')
                                    ->maxLength(255),
                                TextInput::make('phone')
                                    ->label('Телефон')
                                    ->tel()
                                    ->maxLength(255),
                                DatePicker::make('birth_date')
                                    ->label('Дата рождения'),
                                Select::make('group_id')
                                    ->options(Group::all()->pluck('name', 'id'))
                                    ->label('Группа'),
                                Section::make('Адреса')
                                    ->schema([
                                        Repeater::make('addresses')
                                            ->label('')
                                            ->relationship()
                                            ->schema([
                                                TextInput::make('zip')
                                                    ->label('Почтовый индекс'),
                                                TextInput::make('region')
                                                    ->label('Область/край'),
                                                TextInput::make('city')
                                                    ->label('Город'),
                                                TextInput::make('district')
                                                    ->label('Район'),
                                                TextInput::make('street')
                                                    ->label('Улица'),
                                                TextInput::make('house')
                                                    ->label('Дом'),
                                                TextInput::make('corpus')
                                                    ->label('Корпус'),
                                                TextInput::make('room')
                                                    ->label('Квартира'),
                                                TextInput::make('address')
                                                    ->label('Адрес'),
                                                Toggle::make('approve')
                                                    ->label('Подтверждение о проверке'),
                                            ])
                                            ->columns(3),
                                    ]),

                                Section::make('Отзывы')
                                    ->schema([
                                        Repeater::make('reviews')
                                            ->label('')
                                            ->relationship()
                                            ->schema([
                                                Textarea::make('text')
                                                    ->label('Отзыв')
                                                    ->rows(2)
                                                    ->readOnly(),
                                            ])
                                            ->addable(false)
                                            ->deletable(false)
                                            ->columns(1),
                                    ]),

                            ]),
                        Tab::make('Паспортные данные')
                            ->schema([
                                Fieldset::make('passport')
                                    ->relationship('passport')
                                    ->schema([
                                        TextInput::make('passport_number')
                                            ->label('Номер паспорта')
                                            ->maxLength(255),
                                        TextInput::make('series')
                                            ->label('Серия паспорта')
                                            ->maxLength(255),
                                        TextInput::make('issued_by')
                                            ->label('Кем выдан')
                                            ->maxLength(255),
                                        TextInput::make('issued_date')
                                            ->label('Когда выдан')
                                            ->maxLength(255),
                                        TextInput::make('personal_number')
                                            ->label('Личный номер')
                                            ->maxLength(255),
                                        TextInput::make('registration_address')
                                            ->label('Адрес прописки')
                                            ->maxLength(255),
                                    ]),
                            ]),
                        Tab::make('Платежи')
                            ->schema([

                            ]),
                    ])->columns(2)->persistTabInQueryString(),
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        $table->modifyQueryUsing(
            fn (Builder $query) => $query->with(['orders.data'])->orderBy('id', 'desc')
        );

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('first_name')
                    ->label('Имя')
                    ->searchable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->label('Фамилия')
                    ->searchable(),
                Tables\Columns\TextColumn::make('patronymic_name')
                    ->label('Отчество'),
                Tables\Columns\TextColumn::make('email')
                    ->label('E-mail'),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Телефон'),
                Tables\Columns\TextColumn::make('orders')
                    ->label('Сумма покупок')
                    ->getStateUsing(function (User $user) {
                        return $user->completedOrdersCost() . ' руб.';
                    }),
                Tables\Columns\TextColumn::make('group.name')
                    ->label('Группа'),
                Tables\Columns\TextColumn::make('reviews_count')
                    ->label('Кол-во отзывов')
                    ->counts('reviews'),
                Tables\Columns\TextColumn::make('lastAddress.address')
                    ->label('Адрес'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата регистрации')
                    ->dateTime('d.m.Y H:i:s'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->filters([
                QueryBuilder::make()
                    ->constraints([
                        TextConstraint::make('first_name')->label('Имя'),
                        TextConstraint::make('last_name')->label('Фамилия'),
                        TextConstraint::make('patronymic_name')->label('Отчество'),
                        TextConstraint::make('phone')->label('Телефон'),
                        TextConstraint::make('email')->label('E-mail'),
                        SelectConstraint::make('group.id')
                            ->options(Group::query()->pluck('name', 'id'))
                            ->multiple(),
                        TextConstraint::make('addresses.city')->label('Город'),
                        TextConstraint::make('addresses.address')->label('Адрес'),
                    ]),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->deferFilters()
            ->defaultPaginationPageOption(50);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
