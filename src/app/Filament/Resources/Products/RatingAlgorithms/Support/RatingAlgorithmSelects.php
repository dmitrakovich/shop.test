<?php

namespace App\Filament\Resources\Products\RatingAlgorithms\Support;

use App\Models\Category;
use App\Models\Product;
use Filament\Forms\Components\Select;

class RatingAlgorithmSelects
{
    public static function categoryUp(): Select
    {
        return self::categorySelect('category_up_ids', '↑ Категории');
    }

    public static function categoryDown(): Select
    {
        return self::categorySelect('category_down_ids', '↓ Категории');
    }

    public static function productUp(): Select
    {
        return self::productSelect('product_up_ids', '↑ Товары');
    }

    public static function productDown(): Select
    {
        return self::productSelect('product_down_ids', '↓ Товары');
    }

    private static function categorySelect(string $name, string $label): Select
    {
        return Select::make($name)
            ->label($label)
            ->multiple()
            ->searchable()
            ->native(false)
            ->getSearchResultsUsing(fn (?string $search): array => self::searchCategories($search))
            ->getOptionLabelsUsing(fn (array $values): array => self::categoryLabels($values));
    }

    private static function productSelect(string $name, string $label): Select
    {
        return Select::make($name)
            ->label($label)
            ->multiple()
            ->searchable()
            ->native(false)
            ->getSearchResultsUsing(fn (?string $search): array => self::searchProducts($search))
            ->getOptionLabelsUsing(fn (array $values): array => self::productLabels($values));
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
}
