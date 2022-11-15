<?php

namespace App\Models\Feeds;

use App\Facades\Currency as CurrencyFacade;
use App\Models\Category;
use App\Models\Color;
use App\Models\Currency;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class YandexXml extends AbstractFeed
{
    /**
     * @var int
     */
    const DESCRIPTION_MAX_WIDTH = 3000;

    /**
     * Return part of a filename
     *
     * @return string
     */
    public function getKey(): string
    {
        return 'yandex';
    }

    /**
     * Prepare data for xml file
     *
     * @return object
     */
    public function getPreparedData(): object
    {
        return (object) [
            'name' => 'barocco.by',
            'company' => 'ООО «БароккоСтайл»',
            'url' => $this->getHost(),
            'currencies' => $this->getCurrencies(),
            'categories' => $this->getCategories(),
            'offers' => $this->getOffers(),
        ];
    }

    /**
     * Return currencies list with first current currency
     *
     * @return array
     */
    protected function getCurrencies(): array
    {
        $currentCurrency = CurrencyFacade::getCurrentCurrency();
        $currencies[$currentCurrency->code] = 1;

        Currency::where('code', '<>', $currentCurrency->code)
            ->pluck('rate', 'code')
            ->each(function ($rate, $code) use (&$currencies, $currentCurrency) {
                $currencies[$code] = $rate / $currentCurrency->rate;
            });

        return $currencies;
    }

    /**
     * Return catagoies id, name & parent id
     *
     * @return Collection
     */
    protected function getCategories(): Collection
    {
        return $this->getCategoriesList()
            ->where('id', '<>', 1)
            ->map(function (Category $item) {
                $category = [
                    'id' => $item->id,
                    'name' => $item->title,
                ];
                if ($item->parent_id != Category::ROOT_CATEGORY_ID) {
                    $category['parent_id'] = $item->parent_id;
                }

                return (object) $category;
            });
    }

    /**
     * Offers data
     *
     * @return array
     */
    protected function getOffers(): array
    {
        return (new ProductService)->getForFeed()
            ->map(function (Product $item) {
                return (object) [
                    'id' => $item->id,
                    'url' => $this->getHost().$item->getUrl(),
                    'price' => $item->getPrice(),
                    'old_price' => $item->getOldPrice(),
                    'colors' => $this->getColors($item->colors),
                    'params' => $this->getOfferParams($item),
                    'category_id' => $item->category_id,
                    'pictures' => $this->getProductImages($item->getMedia()),
                    'type_prefix' => $item->category->name,
                    'vendor' => $this->xmlSpecialChars($item->brand->name),
                    'model' => $this->xmlSpecialChars($item->sku),
                    'description' => $this->getDescription($item),
                ];
            })->toArray();
    }

    /**
     * Prepare color from colors for filters
     *
     * @param  EloquentCollection  $colors
     * @return array
     */
    public function getColors(EloquentCollection $colors): array
    {
        return $colors->map(function (Color $color) {
            return $color->name;
        })->toArray();
    }

    /**
     * Prepare offer params
     *
     * @param  Product  $product
     * @return array
     */
    public function getOfferParams(Product $product): array
    {
        $params = [];
        if (! empty($product->fabric_top_txt)) {
            $name = 'Материал';
            if ($product->category->parent_id != Category::ACCESSORIES_PARENT_ID) {
                $name .= ' верха';
            }
            $params[$name] = $product->fabric_top_txt;
        }

        if (! empty($product->fabric_insole_txt)) {
            $params['Материал подкладки'] = $product->fabric_insole_txt;
        }

        if (! empty($product->fabric_outsole_txt)) {
            $params['Материал подошвы'] = $product->fabric_outsole_txt;
        }

        if (! empty($product->heel_txt)) {
            $params['Высота каблука'] = $product->heel_txt;
        }

        return $params;
    }

    /**
     * Generate product description
     *
     * @param  Product  $product
     * @return string
     */
    public function getDescription(Product $product): string
    {
        $description = $product->description;

        $description = trim(strip_tags($description));
        $description = Str::limit($description, self::DESCRIPTION_MAX_WIDTH - 3, '...');

        return $description;
    }
}
