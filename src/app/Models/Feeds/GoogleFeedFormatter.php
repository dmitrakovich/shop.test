<?php

namespace App\Models\Feeds;

use App\Facades\Currency as CurrencyFacade;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

/**
 * Заголовки, описания и материал для Google Merchant (XML/CSV).
 */
final class GoogleFeedFormatter
{
    public const TITLE_MAX_LENGTH = 150;

    /**
     * Цвет для title / g:color (как в GoogleXml::getColor).
     */
    public static function colorLabel(EloquentCollection $colors): string
    {
        return $colors->count() === 1 ? $colors[0]->name : 'разноцветный';
    }

    /**
     * Формат: «Тип обуви Бренд, материал, цвет, р. размер» — без внутреннего ID.
     */
    public static function title(Product $product, string $colorForFeed): string
    {
        $parts = [];

        $base = trim($product->category->name . ' ' . $product->brand->name);
        if ($base !== '') {
            $parts[] = $base;
        }

        if (!empty($product->fabric_top_txt)) {
            $parts[] = trim($product->fabric_top_txt);
        }

        if ($colorForFeed !== '') {
            $parts[] = $colorForFeed === 'разноцветный'
                ? $colorForFeed
                : mb_strtolower($colorForFeed);
        }

        $sizesShort = self::formatSizesShort($product->sizes);
        if ($sizesShort !== '') {
            $parts[] = 'р. ' . $sizesShort;
        }

        $title = implode(', ', array_filter($parts));
        if (mb_strlen($title) > self::TITLE_MAX_LENGTH) {
            $title = mb_substr($title, 0, self::TITLE_MAX_LENGTH - 1) . '…';
        }

        return $title;
    }

    /**
     * Размеры для заголовка: перечисление через запятую (порядок как в каталоге).
     */
    public static function formatSizesShort(EloquentCollection $sizes): string
    {
        return $sizes->pluck('name')
            ->filter(fn (?string $n) => $n !== null && $n !== '')
            ->implode(', ');
    }

    /**
     * Развёрнутое описание (ориентир 500+ символов при наличии данных).
     */
    public static function description(Product $product): string
    {
        $currency = CurrencyFacade::getCurrentCurrency();
        $discount = ($product->old_price > 0)
            ? (int)round(($product->old_price - $product->price) / $product->old_price * 100)
            : 0;

        $chunks = [];

        $fromCard = $product->description
            ? trim(preg_replace('/\s+/u', ' ', strip_tags($product->description)))
            : '';
        if ($fromCard !== '') {
            $chunks[] = $fromCard;
        }

        $lead = trim($product->category->name . ' ' . $product->brand->name) . '.';
        $chunks[] = $lead;

        if ($discount >= 10) {
            $chunks[] = "Скидка {$discount}% от прежней цены.";
        }

        $sizesHuman = self::sizesLineForDescription($product->sizes);
        if ($sizesHuman !== '') {
            $chunks[] = $sizesHuman;
        }

        $materialBlock = self::materialDetailsParagraph($product);
        if ($materialBlock !== '') {
            $chunks[] = $materialBlock;
        }

        if (!empty($product->heel_txt)) {
            $chunks[] = 'Каблук: ' . trim($product->heel_txt) . '.';
        }

        if (!empty($product->key_features)) {
            $chunks[] = trim($product->key_features);
        }

        if (!empty($product->product_features)) {
            $chunks[] = trim($product->product_features);
        }

        $colorTxt = $product->color_txt ? trim($product->color_txt) : '';
        if ($colorTxt !== '') {
            $chunks[] = 'Цвет: ' . $colorTxt . '.';
        }

        $chunks[] = 'Цена ' . $product->getPrice() . ' ' . $currency->symbol . '.';

        $text = implode(' ', array_unique(array_filter($chunks)));

        if (mb_strlen($text) < 450) {
            $text .= ' Товар представлен в каталоге интернет-магазина Barocco: подбор по размеру, доставка по Беларуси, возможность примерки. Уточняйте наличие интересующего размера на странице товара.';
        }

        $text = trim(preg_replace('/\s+/u', ' ', $text));

        return str_replace(']]>', ']] >', $text);
    }

    /**
     * g:material — верх, затем связка fabrics, затем текстовые поля подошвы/подкладки.
     */
    public static function material(Product $product): string
    {
        if (!empty($product->fabric_top_txt)) {
            return trim($product->fabric_top_txt);
        }

        if ($product->relationLoaded('fabrics') && $product->fabrics->isNotEmpty()) {
            return $product->fabrics->pluck('name')->unique()->filter()->implode(', ');
        }

        $fallback = array_filter([
            $product->fabric_inner_txt,
            $product->fabric_insole_txt,
            $product->fabric_outsole_txt,
        ], fn (?string $s) => $s !== null && trim($s) !== '');

        if ($fallback !== []) {
            return implode(', ', array_map('trim', $fallback));
        }

        return '';
    }

    protected static function materialDetailsParagraph(Product $product): string
    {
        $sentences = [];

        if (!empty($product->fabric_top_txt)) {
            $sentences[] = 'Верх: ' . trim($product->fabric_top_txt) . '.';
        }
        if (!empty($product->fabric_inner_txt)) {
            $sentences[] = 'Подкладка: ' . trim($product->fabric_inner_txt) . '.';
        }
        if (!empty($product->fabric_insole_txt)) {
            $sentences[] = 'Стелька: ' . trim($product->fabric_insole_txt) . '.';
        }
        if (!empty($product->fabric_outsole_txt)) {
            $sentences[] = 'Подошва: ' . trim($product->fabric_outsole_txt) . '.';
        }

        return $sentences === [] ? '' : implode(' ', $sentences);
    }

    protected static function sizesLineForDescription(EloquentCollection $sizes): string
    {
        $names = $sizes->pluck('name')->filter(fn (?string $n) => $n !== null && $n !== '')->values();
        if ($names->isEmpty()) {
            return '';
        }

        if ($names->count() === 1 && $sizes->first()?->id === 1) {
            return '';
        }

        return 'Доступные размеры (EU): ' . $names->implode(', ') . '.';
    }
}
