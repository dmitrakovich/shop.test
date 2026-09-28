<?php

namespace App\Filament\Resources\Docs;

use App\Enums\Filament\NavGroup;
use App\Filament\Resources\Docs\Pages\CreateDoc;
use App\Filament\Resources\Docs\Pages\EditDoc;
use App\Filament\Resources\Docs\Pages\ListDocs;
use App\Filament\Resources\Docs\Pages\ViewDoc;
use App\Filament\Resources\Docs\Schemas\DocForm;
use App\Filament\Resources\Docs\Schemas\DocInfolist;
use App\Filament\Resources\Docs\Tables\DocsTable;
use App\Models\Doc;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use function Filament\Support\original_request;

class DocResource extends Resource
{
    protected static ?string $model = Doc::class;

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::Settings;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedPencilSquare;

    protected static ?string $navigationLabel = 'Документация';

    protected static ?string $modelLabel = 'Документ';

    protected static ?string $pluralModelLabel = 'Документация';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $slug = 'docs';

    protected static ?int $navigationSort = 7;

    public static function form(Schema $schema): Schema
    {
        return DocForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DocInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DocsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDocs::route('/'),
            'create' => CreateDoc::route('/create'),
            'edit' => EditDoc::route('/{record}/edit'),
            'view' => ViewDoc::route('/{record}'),
        ];
    }

    /**
     * @return array<NavigationItem>
     */
    public static function getNavigationItems(): array
    {
        $editItems = array_map(
            fn (NavigationItem $item): NavigationItem => $item->isActiveWhen(
                fn (): bool => original_request()->routeIs([
                    static::getRouteBaseName() . '.index',
                    static::getRouteBaseName() . '.create',
                    static::getRouteBaseName() . '.edit',
                ]),
            ),
            parent::getNavigationItems(),
        );

        $docItems = Doc::query()
            ->orderBy('sort')
            ->orderBy('id')
            ->get()
            ->map(fn (Doc $doc): NavigationItem => NavigationItem::make($doc->title)
                ->group(NavGroup::Docs)
                ->icon(self::iconFor($doc))
                ->sort($doc->sort)
                ->url(fn (): string => static::getUrl('view', ['record' => $doc]))
                ->isActiveWhen(function () use ($doc): bool {
                    $request = original_request();

                    return $request->routeIs(static::getRouteBaseName() . '.view')
                        && (string)$request->route('record') === $doc->slug;
                }))
            ->all();

        return [
            ...$docItems,
            ...$editItems,
        ];
    }

    private static function iconFor(Doc $doc): Heroicon
    {
        return match ($doc->slug) {
            'manager_instagram' => Heroicon::OutlinedCamera,
            'manager_ordercheckout' => Heroicon::OutlinedShoppingCart,
            'manager_manual_addproduct' => Heroicon::OutlinedRectangleStack,
            'manager_script' => Heroicon::OutlinedChatBubbleLeftRight,
            'manager_utm' => Heroicon::OutlinedLink,
            'fotograph_manual' => Heroicon::OutlinedCamera,
            'manager_manual_orderstep' => Heroicon::OutlinedQueueList,
            'test' => Heroicon::OutlinedPaperAirplane,
            'rating' => Heroicon::OutlinedChartBar,
            default => Heroicon::OutlinedDocumentText,
        };
    }
}
