<?php

namespace App\Filament\Resources\User\Users;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Fieldset;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use App\Filament\Resources\User\Users\Pages\ListUsers;
use App\Filament\Resources\User\Users\Pages\CreateUser;
use App\Filament\Resources\User\Users\Pages\EditUser;
use App\Enums\Filament\NavGroup;
use App\Enums\User\OrderType;
use App\Filament\Actions\ToggleDeviceBanAction;
use App\Filament\Components\Forms\RelationManager;
use App\Filament\Resources\User\UserResource\Pages;
use App\Filament\Resources\User\Users\RelationManagers\BlacklistRelationManager;
use App\Filament\Resources\User\Users\RelationManagers\PaymentsRelationManager;
use App\Models\User\Group;
use App\Models\User\User;
use App\ValueObjects\Phone;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\SelectConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string | \UnitEnum | null $navigationGroup = NavGroup::USER;

    protected static ?string $modelLabel = 'Пользователи';

    protected static ?string $pluralModelLabel = 'Пользователи';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Tabs')->tabs([
                    Tab::make('Основная информация')->schema([
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
                            ->formatStateUsing(fn (?Phone $state): string => $state?->toE164())
                            ->tel()
                            ->maxLength(255),
                        DatePicker::make('birth_date')
                            ->label('Дата рождения'),
                        Select::make('group_id')
                            ->options(Group::all()->pluck('name', 'id'))
                            ->label('Группа'),
                        Section::make('Адреса')->schema([
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

                        Section::make('Отзывы')->schema([
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
                    Tab::make('Паспортные данные')->schema([
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
                    Tab::make('Платежи')->schema([
                        RelationManager::make()->manager(PaymentsRelationManager::class)->lazy(true),
                    ])
                        ->columns(1),
                    Tab::make('Черный список (Лог)')->schema([
                        RelationManager::make()->manager(BlacklistRelationManager::class)->lazy(true),
                    ])->columns(1),
                ])->columns(2)->persistTabInQueryString(),
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')
                    ->label('ФИО')
                    ->getStateUsing(fn (User $user) => $user->getFullName())
                    ->searchable(query: function (Builder $query, $search) {
                        $nameColumns = ['first_name', 'last_name', 'patronymic_name'];
                        $query->whereAny($nameColumns, 'like', "%$search%");
                    }),
                TextColumn::make('phone')
                    ->label('Телефон'),
                TextColumn::make('metadata.last_order_type')
                    ->label('Тип последнего заказа')
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
                TextColumn::make('orders')
                    ->label('Сумма покупок')
                    ->getStateUsing(fn (User $user) => $user->completedOrdersCost())
                    ->suffix(' руб.'),
                TextColumn::make('metadata.last_order_date')
                    ->label('Дата последнего заказа')
                    ->dateTime('d.m.Y H:i:s')
                    ->toggleable()
                    ->toggledHiddenByDefault(),
                TextColumn::make('email')
                    ->label('E-mail')
                    ->toggleable()
                    ->toggledHiddenByDefault(),
                TextColumn::make('group.name')
                    ->label('Группа'),
                TextColumn::make('reviews_count')
                    ->label('Кол-во отзывов')
                    ->counts('reviews'),
                TextColumn::make('lastAddress.address')
                    ->label('Адрес')
                    ->toggleable()
                    ->toggledHiddenByDefault(),
                TextColumn::make('birth_date')
                    ->label('День рождения')
                    ->dateTime('j F')
                    ->toggleable()
                    ->toggledHiddenByDefault(),
                TextColumn::make('created_at')
                    ->label('Дата регистрации')
                    ->dateTime('d.m.Y H:i:s'),
            ])
            ->defaultSort('id', 'desc')
            ->modifyQueryUsing(
                fn (Builder $query) => $query->with(['orders.data', 'devices'])
            )
            ->recordActions([
                EditAction::make(),
                ToggleDeviceBanAction::make(),
            ])
            ->filters([
                SelectFilter::make('last_order_type')
                    ->label('Тип последнего заказа')
                    ->options(OrderType::class)
                    ->query(function (Builder $query, array $data) {
                        if ($data['value']) {
                            $query->whereRelation('metadata', 'last_order_type', $data['value']);
                        }
                    }),
                Filter::make('order_date')
                    ->schema([
                        Fieldset::make()
                            ->label('Совершали покупки')
                            ->schema([
                                DatePicker::make('ordered_from')
                                    ->label('с:')
                                    ->native(false)
                                    ->closeOnDateSelection(),
                                DatePicker::make('ordered_until')
                                    ->label('по:')
                                    ->native(false)
                                    ->closeOnDateSelection(),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['ordered_from']) {
                            $query->whereRelation('metadata', 'last_order_date', '>=', $data['ordered_from']);
                        }
                        if ($data['ordered_until']) {
                            $query->whereRelation('metadata', 'last_order_date', '<=', $data['ordered_until']);
                        }
                    }),
                Filter::make('birth_date')
                    ->schema([
                        Fieldset::make()
                            ->label('День рождения')
                            ->schema([
                                DatePicker::make('birth_date_from')
                                    ->label('с:')
                                    ->native(false)
                                    ->closeOnDateSelection(),
                                DatePicker::make('birth_date_until')
                                    ->label('по:')
                                    ->native(false)
                                    ->closeOnDateSelection(),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['birth_date_from']) {
                            $from = Carbon::parse($data['birth_date_from']);
                            $query->where(function (Builder $query) use ($from) {
                                $query->where(function (Builder $query) use ($from) {
                                    $query->whereMonth('birth_date', '>=', $from->month)
                                        ->whereDay('birth_date', '>=', $from->day);
                                })->orWhere(function (Builder $query) use ($from) {
                                    $query->whereMonth('birth_date', '>', $from->month);
                                });
                            });
                        }
                        if ($data['birth_date_until']) {
                            $until = Carbon::parse($data['birth_date_until']);
                            $query->where(function (Builder $query) use ($until) {
                                $query->where(function (Builder $query) use ($until) {
                                    $query->whereMonth('birth_date', '<=', $until->month)
                                        ->whereDay('birth_date', '<=', $until->day);
                                })->orWhere(function (Builder $query) use ($until) {
                                    $query->whereMonth('birth_date', '<', $until->month);
                                });
                            });
                        }
                    }),
                Filter::make('register_date')
                    ->schema([
                        Fieldset::make()
                            ->label('Дата регистрации')
                            ->schema([
                                DatePicker::make('registered_from')
                                    ->label('с:')
                                    ->native(false)
                                    ->closeOnDateSelection(),
                                DatePicker::make('registered_until')
                                    ->label('по:')
                                    ->native(false)
                                    ->closeOnDateSelection(),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['registered_from']) {
                            $query->where('created_at', '>=', $data['registered_from']);
                        }
                        if ($data['registered_until']) {
                            $query->where('created_at', '<=', $data['registered_until']);
                        }
                    }),
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
            // ->deferFilters()
            ->defaultPaginationPageOption(50);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
