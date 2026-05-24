<?php

namespace App\Filament\Resources\Products\RatingAlgorithms\Pages;

use App\Filament\Resources\Products\RatingAlgorithms\RatingAlgorithmResource;
use App\Jobs\UpdateProductsRatingJob;
use App\Models\Category;
use App\Models\Config;
use App\Models\Product;
use App\Models\RatingAlgorithm;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListRatingAlgorithms extends ListRecords
{
    protected static string $resource = RatingAlgorithmResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->settingsAction(),
            $this->recalculateAction(),
            CreateAction::make(),
        ];
    }

    private function settingsAction(): Action
    {
        return Action::make('settings')
            ->label('Настройки рейтинга')
            ->modalWidth('4xl')
            ->fillForm(fn (): array => self::ratingConfig())
            ->form([
                Select::make('popularity_algorithm_id')
                    ->label('Алгоритм для популярности')
                    ->options(fn () => RatingAlgorithm::query()->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->native(false)
                    ->required(),
                Select::make('newness_algorithm_id')
                    ->label('Алгоритм для новинок')
                    ->options(fn () => RatingAlgorithm::query()->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->native(false)
                    ->required(),
                Select::make('category_up_ids')
                    ->label('Категории на повышение')
                    ->multiple()
                    ->searchable()
                    ->native(false)
                    ->getSearchResultsUsing(fn (?string $search): array => self::searchCategories($search))
                    ->getOptionLabelsUsing(fn (array $values): array => self::categoryLabels($values)),
                Select::make('category_down_ids')
                    ->label('Категории на понижение')
                    ->multiple()
                    ->searchable()
                    ->native(false)
                    ->getSearchResultsUsing(fn (?string $search): array => self::searchCategories($search))
                    ->getOptionLabelsUsing(fn (array $values): array => self::categoryLabels($values)),
                Select::make('product_up_ids')
                    ->label('Товары на повышение')
                    ->multiple()
                    ->searchable()
                    ->native(false)
                    ->getSearchResultsUsing(fn (?string $search): array => self::searchProducts($search))
                    ->getOptionLabelsUsing(fn (array $values): array => self::productLabels($values)),
                Select::make('product_down_ids')
                    ->label('Товары на понижение')
                    ->multiple()
                    ->searchable()
                    ->native(false)
                    ->getSearchResultsUsing(fn (?string $search): array => self::searchProducts($search))
                    ->getOptionLabelsUsing(fn (array $values): array => self::productLabels($values)),
            ])
            ->action(function (array $data): void {
                Config::query()->updateOrCreate(
                    ['key' => 'rating'],
                    ['config' => self::normalizeConfig([...self::ratingConfig(), ...$data])]
                );

                Notification::make()
                    ->title('Настройки рейтинга сохранены')
                    ->success()
                    ->send();
            });
    }

    private function recalculateAction(): Action
    {
        return Action::make('recalculate')
            ->label('Пересчитать рейтинг')
            ->requiresConfirmation()
            ->action(function (): void {
                UpdateProductsRatingJob::dispatchSync();

                Notification::make()
                    ->title('Рейтинг пересчитан')
                    ->success()
                    ->send();
            });
    }

    /**
     * @return array<string, mixed>
     */
    private static function ratingConfig(): array
    {
        $configModel = Config::query()->find('rating');
        $config = $configModel instanceof Config ? $configModel->config : [];

        return self::normalizeConfig($config);
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    private static function normalizeConfig(array $config): array
    {
        return [
            'popularity_algorithm_id' => isset($config['popularity_algorithm_id']) ? (int)$config['popularity_algorithm_id'] : null,
            'newness_algorithm_id' => isset($config['newness_algorithm_id']) ? (int)$config['newness_algorithm_id'] : null,
            'category_up_ids' => self::ids($config['category_up_ids'] ?? []),
            'category_down_ids' => self::ids($config['category_down_ids'] ?? []),
            'product_up_ids' => self::ids($config['product_up_ids'] ?? []),
            'product_down_ids' => self::ids($config['product_down_ids'] ?? []),
            'last_update' => $config['last_update'] ?? null,
        ];
    }

    /**
     * @return list<int>
     */
    private static function ids(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        return array_values(array_unique(array_map('intval', array_filter($value, 'is_numeric'))));
    }

    /**
     * @return array<int, string>
     */
    private static function searchCategories(?string $search): array
    {
        return Category::query()
            ->when($search, fn ($query) => $query->where('title', 'like', "%{$search}%"))
            ->limit(50)
            ->pluck('title', 'id')
            ->all();
    }

    /**
     * @param  array<array-key, mixed>  $values
     * @return array<int, string>
     */
    private static function categoryLabels(array $values): array
    {
        return Category::query()
            ->whereIn('id', self::ids($values))
            ->pluck('title', 'id')
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private static function searchProducts(?string $search): array
    {
        return Product::query()
            ->when($search, function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('id', 'like', "{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('id')
            ->limit(50)
            ->get(['id', 'sku'])
            ->mapWithKeys(fn (Product $product): array => [$product->id => self::productLabel($product)])
            ->all();
    }

    /**
     * @param  array<array-key, mixed>  $values
     * @return array<int, string>
     */
    private static function productLabels(array $values): array
    {
        return Product::query()
            ->whereIn('id', self::ids($values))
            ->get(['id', 'sku'])
            ->mapWithKeys(fn (Product $product): array => [$product->id => self::productLabel($product)])
            ->all();
    }

    private static function productLabel(Product $product): string
    {
        return "{$product->id} ({$product->sku})";
    }
}
