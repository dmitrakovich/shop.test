<?php

namespace App\Filament\Pages\Settings;

use App\Enums\Config\ConfigKey;
use App\Filament\Pages\Settings\Concerns\ConfigClusterPage;
use App\Filament\Pages\Settings\Concerns\ManagesConfigForm;
use App\Models\AvailableSizes;
use Filament\Forms\Components\TagsInput;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class InventoryBlacklistSettings extends Page
{
    use ConfigClusterPage;
    use ManagesConfigForm;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedNoSymbol;

    protected static ?string $navigationLabel = 'Исключённые категории 1С';

    protected static ?string $title = 'Категории, исключённые из синхронизации с 1С';

    protected ?string $subheading = 'Остатки товаров из этих категорий 1С не загружаются: их не будет в «Складе», «Новых товарах из 1С» и в наличии на сайте';

    protected static ?string $slug = 'inventory-blacklist';

    protected static ?int $navigationSort = 6;

    protected static function configKey(): ConfigKey
    {
        return ConfigKey::InventoryBlacklist;
    }

    protected function getSavedNotificationTitle(): string
    {
        return 'Список исключённых категорий сохранён';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TagsInput::make('categories')
                            ->label('Категории 1С')
                            ->helperText('Название должно точно совпадать с категорией в 1С. В подсказках — категории из последней выгрузки остатков.')
                            ->placeholder('Добавить категорию')
                            ->suggestions(fn (): array => $this->getOneCCategorySuggestions())
                            ->trim()
                            ->splitKeys(['Tab'])
                            ->dehydrateStateUsing(fn (mixed $state): array => $this->normalizeCategories($state)),
                    ]),
            ]);
    }

    /**
     * @return list<string>
     */
    private function getOneCCategorySuggestions(): array
    {
        return AvailableSizes::query()
            ->whereNotNull('category_name')
            ->distinct()
            ->orderBy('category_name')
            ->pluck('category_name')
            ->all();
    }

    /**
     * @return list<string>
     */
    private function normalizeCategories(mixed $state): array
    {
        $categories = [];

        foreach ((array)$state as $category) {
            $category = trim((string)$category);

            if ($category === '' || in_array($category, $categories, true)) {
                continue;
            }

            $categories[] = $category;
        }

        return $categories;
    }
}
