<?php

namespace App\Filament\Resources\Products\Products\Schemas;

use App\Enums\Product\ProductLabel;
use App\Models\TagGroup;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\ImageEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Operation;
use Illuminate\Database\Eloquent\Builder;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {

        // todo: $productFromStock = self::getStockProduct();

        return $schema
            ->components([
                Group::make()
                    ->schema([
                        Section::make()
                            ->schema([
                                TextInput::make('sku')
                                    ->label('Артикул')
                                    ->required(),
                                TextInput::make('slug')
                                    ->default('temp_slug_' . time())
                                    ->readOnly(),
                                RichEditor::make('description')
                                    ->label('Описание')
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),
                        Section::make('Цены')
                            ->schema([
                                TextInput::make('price')
                                    ->required()
                                    ->label('Цена')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(0.1)
                                    ->prefix('BYN'),
                                TextInput::make('old_price')
                                    ->label('Старая цена')
                                    ->required()
                                    ->numeric()
                                    ->default(0.0)
                                    ->prefix('BYN'),
                            ])
                            ->columns(2),
                        Section::make('Фото')
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('media')
                                    ->panelLayout('grid')
                                    ->image()
                                    ->disk('media')
                                    ->multiple()
                                    ->openable()
                                    ->conversion('normal')
                                    ->reorderable()
                                    ->downloadable()
                                    ->hiddenLabel(),
                                Repeater::make('media_properties')
                                    ->relationship('media')
                                    ->label('Характеристики фото')
                                    ->visibleOn(Operation::Edit)
                                    ->schema([
                                        ImageEntry::make('preview0')
                                            ->state(fn (Media $record) => $record->getUrl('thumb'))
                                            ->hiddenLabel()
                                            ->imageHeight(80)
                                            ->square(),
                                        Toggle::make('generated_conversions.is_imidj')
                                            ->label('Имиджевое'),
                                    ])
                                    ->deletable(false)
                                    ->addable(false)
                                    ->columns(3),
                            ]),

                        // todo: для видео репитер
                        // Repeater::make('video')
                        //     ->schema([
                        //         FileUpload::make('preview')
                        //             ->label('Превью')
                        //             ->image()
                        //             ->required(),

                        //         TextInput::make('url')
                        //             ->label('Ссылка на видео')
                        //             ->nullable(),
                        //     ])
                        //     ->columns(2)
                        //     ->addActionLabel('Add image')
                        //     ->collapsible(),
                    ])
                    ->columnSpan(['lg' => 2]),
                Group::make()
                    ->schema([
                        Section::make('Характеристики')
                            ->schema([
                                // TextInput::make('one_c_id'),
                                // TextInput::make('rating'),
                                Select::make('category_id')
                                    ->label('Категория')
                                    ->relationship('category', 'title')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Select::make('brand_id')
                                    ->label('Бренд')
                                    ->relationship('brand', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Select::make('manufacturer_id')
                                    ->label('Производитель')
                                    ->relationship('manufacturer', 'name')
                                    ->searchable()
                                    ->preload(),
                                Select::make('sizes')
                                    ->label('Размеры')
                                    ->multiple()
                                    ->relationship('sizes', 'name', function (Builder $query) {
                                        $query->where('is_active', true);
                                    })
                                    ->preload()
                                    ->required(),
                                Select::make('collection_id')
                                    ->label('Коллекция')
                                    ->relationship('collection', 'name', function (Builder $query) {
                                        $query->orderByDesc('id');
                                    })
                                    ->native(false)
                                    ->required(),
                                Select::make('season_id')
                                    ->label('Сезон')
                                    ->relationship('season', 'name')
                                    ->required()
                                    ->native(false),
                                Select::make('colors')
                                    ->label('Цвет для фильтра')
                                    ->multiple()
                                    ->relationship('colors', 'name')
                                    ->preload(),
                                Select::make('fabrics')
                                    ->label('Материал для фильтра')
                                    ->multiple()
                                    ->relationship('fabrics', 'name')
                                    ->preload(),
                                Select::make('styles')
                                    ->label('Стиль')
                                    ->multiple()
                                    ->relationship('styles', 'name')
                                    ->preload(),
                                Select::make('heels')
                                    ->label('Тип каблука/подошвы')
                                    ->multiple()
                                    ->relationship('heels', 'name')
                                    ->preload(),
                                Select::make('country_of_origin_id')
                                    ->label('Страна производитель')
                                    ->relationship('countryOfOrigin', 'name')
                                    ->native(false),
                            ]),
                        Section::make('Описание')
                            ->schema([
                                TextInput::make('color_txt')->label('Цвет'),
                                TextInput::make('fabric_top_txt')->label('Материал верха'),
                                TextInput::make('fabric_inner_txt')->label('Материал внутри'),
                                TextInput::make('fabric_insole_txt')->label('Материал стельки'),
                                TextInput::make('fabric_outsole_txt')->label('Материал подошвы'),
                                TextInput::make('bootleg_height_txt')->label('Высота голенища'),
                                TextInput::make('heel_txt')->label('Высота каблука/подошвы'),
                            ]),

                        Section::make()
                            ->schema([
                                Select::make('label_id')
                                    ->label('Метка')
                                    ->options(ProductLabel::class)
                                    ->native(false),
                                TextInput::make('product_features')
                                    ->label('Ключевая особенность'),
                                TextInput::make('key_features')
                                    ->label('Ключевая особенность модели (для промта)'),
                                Select::make('tags')
                                    ->relationship('tags', 'name')
                                    ->label('Теги')
                                    ->multiple()
                                    ->searchable(false)
                                    ->options([])
                                    ->suffixAction(
                                        Action::make('selectTags')
                                            ->icon('heroicon-m-tag')
                                            ->label('Выбрать')
                                            ->modalHeading('Выбор тегов')
                                            ->modalSubmitActionLabel('Сохранить')
                                            ->schema(function (Get $get) {
                                                $components = [];
                                                $selectedTags = $get('tags');
                                                foreach (TagGroup::with('tags')->get() as $group) {
                                                    if (!$options = $group->tags->pluck('name', 'id')->toArray()) {
                                                        continue;
                                                    }

                                                    $components[] = CheckboxList::make("group_{$group->id}_tags")
                                                        ->label($group->name)
                                                        ->options($options)
                                                        ->columns(4)
                                                        ->default(array_intersect($selectedTags, array_keys($options)));
                                                }

                                                return $components;
                                            })
                                            ->action(function (array $data, Set $set): void {
                                                $set('tags', collect($data)
                                                    ->filter()
                                                    ->flatMap(fn ($value) => is_array($value) ? $value : [])
                                                    ->map('intval')
                                                    ->unique()
                                                    ->values()
                                                    ->toArray());
                                            })),
                            ]),

                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    // protected static function getStockProduct(): AvailableSizes
    // {
    //     if (empty($stockIds = request('stock_ids'))) {
    //         return new AvailableSizes();
    //     }

    //     return AvailableSizes::query()
    //         ->selectRaw(implode(', ', [
    //             'sku',
    //             'brand_id',
    //             'category_id',
    //             'MAX(buy_price) as buy_price',
    //             'MAX(sell_price) as sell_price',
    //             implode(', ', AvailableSizes::getSumWrappedSizeFields()),
    //         ]))
    //         ->groupBy(['sku', 'brand_id', 'category_id'])
    //         ->whereIn('id', explode(',', $stockIds))
    //         ->first();
    // }
}
