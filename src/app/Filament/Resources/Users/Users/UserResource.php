<?php

namespace App\Filament\Resources\Users\Users;

use App\Enums\Filament\NavGroup;
use App\Enums\User\OrderType;
use App\Filament\Actions\ToggleDeviceBanAction;
use App\Filament\Components\Forms\RelationManager;
use App\Filament\Resources\Users\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Users\Pages\EditUser;
use App\Filament\Resources\Users\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Users\RelationManagers\BlacklistRelationManager;
use App\Filament\Resources\Users\Users\RelationManagers\PaymentsRelationManager;
use App\Models\Country;
use App\Models\User\Group;
use App\Models\User\User;
use App\ValueObjects\Phone;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Operation;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\SelectConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::Users;

    protected static ?string $modelLabel = 'Пользователи';

    protected static ?string $pluralModelLabel = 'Пользователи';

    protected static ?int $navigationSort = 5;

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
                            ->email()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label('Телефон')
                            ->formatStateUsing(fn (?Phone $state): ?string => $state?->toE164())
                            ->tel()
                            ->required()
                            ->maxLength(255),
                        DatePicker::make('birth_date')
                            ->label('Дата рождения')
                            ->native(false),
                        Select::make('group_id')
                            ->options(fn () => Group::query()->pluck('name', 'id'))
                            ->label('Группа')
                            ->required()
                            ->native(false),
                        Section::make('Адреса')->schema([
                            Repeater::make('addresses')
                                ->label('')
                                ->relationship()
                                ->schema([
                                    Grid::make(12)->schema([
                                        Select::make('country_id')
                                            ->label('Страна')
                                            ->options(fn () => Country::query()->orderBy('name')->pluck('name', 'id'))
                                            ->native(false)
                                            ->columnSpan(8),
                                        TextInput::make('zip')
                                            ->label('Индекс')
                                            ->columnSpan(4),
                                        TextInput::make('region')
                                            ->label('Область / край')
                                            ->columnSpan(6),
                                        TextInput::make('district')
                                            ->label('Район')
                                            ->columnSpan(6),
                                        TextInput::make('city')
                                            ->label('Город')
                                            ->columnSpan(12),
                                        TextInput::make('street')
                                            ->label('Улица')
                                            ->columnSpan(12),
                                        TextInput::make('house')
                                            ->label('Дом')
                                            ->columnSpan(4),
                                        TextInput::make('corpus')
                                            ->label('Корпус')
                                            ->columnSpan(4),
                                        TextInput::make('room')
                                            ->label('Квартира')
                                            ->columnSpan(4),
                                        TextInput::make('address')
                                            ->label('Адрес одной строкой')
                                            ->columnSpan(12),
                                        Toggle::make('approve')
                                            ->label('Адрес проверен')
                                            ->inline(false)
                                            ->columnSpan(12),
                                    ]),
                                ])
                                ->collapsible()
                                ->itemLabel(function (array $state): string {
                                    if (filled($state['address'] ?? null)) {
                                        return (string) $state['address'];
                                    }

                                    $parts = array_filter([
                                        $state['city'] ?? null,
                                        isset($state['street']) ? 'ул. ' . $state['street'] : null,
                                        isset($state['house']) ? 'д. ' . $state['house'] : null,
                                    ]);

                                    $label = trim(implode(', ', $parts));

                                    return $label !== '' ? $label : 'Новый адрес';
                                })
                                ->defaultItems(0)
                                ->addActionLabel('Добавить адрес'),
                        ])->columnSpanFull(),

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
                        ])->columnSpanFull(),

                    ]),
                    Tab::make('Паспортные данные')->schema([
                        Fieldset::make('passport')
                            ->relationship('passport')
                            ->schema([
                                TextInput::make('passport_number')
                                    ->label('Номер паспорта')
                                    ->maxLength(255)
                                    ->required(fn (Get $get): bool => self::passportHasAnyValue($get))
                                    ->live(onBlur: true),
                                TextInput::make('series')
                                    ->label('Серия паспорта')
                                    ->maxLength(255)
                                    ->required(fn (Get $get): bool => self::passportHasAnyValue($get)),
                                TextInput::make('issued_by')
                                    ->label('Кем выдан')
                                    ->maxLength(255)
                                    ->required(fn (Get $get): bool => self::passportHasAnyValue($get)),
                                DatePicker::make('issued_date')
                                    ->label('Когда выдан')
                                    ->native(false)
                                    ->required(fn (Get $get): bool => self::passportHasAnyValue($get)),
                                TextInput::make('personal_number')
                                    ->label('Личный номер')
                                    ->maxLength(255)
                                    ->required(fn (Get $get): bool => self::passportHasAnyValue($get)),
                                TextInput::make('registration_address')
                                    ->label('Адрес прописки')
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),
                    ]),
                    Tab::make('Платежи')->hiddenOn(Operation::Create)->schema([
                        RelationManager::make()->manager(PaymentsRelationManager::class)->lazy(true),
                    ])
                        ->columns(1),
                    Tab::make('Черный список (Лог)')->hiddenOn(Operation::Create)->schema([
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

    private static function passportHasAnyValue(Get $get): bool
    {
        foreach (['passport_number', 'series', 'issued_by', 'issued_date', 'personal_number', 'registration_address'] as $field) {
            if (filled($get($field))) {
                return true;
            }
        }

        return false;
    }
}
