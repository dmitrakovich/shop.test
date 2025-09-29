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

class YandexXml extends AbstractFeed
{
    /**
     * @var int
     */
    const DESCRIPTION_MAX_WIDTH = 3000;

    /**
     * Return part of a filename
     */
    public function getKey(): string
    {
        return 'yandex';
    }

    /**
     * Prepare data for xml file
     */
    public function getPreparedData(): object
    {
        return (object)[
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
     * Return categories id, name & parent id
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

                return (object)$category;
            });
    }

    /**
     * Offers data
     */
    protected function getOffers(): array
    {
        return (new ProductService())->getForFeed()
            ->map(function (Product $item) {
                $media = $this->getProductMedia($item->getMedia());

                return (object)[
                    'id' => $item->id,
                    'url' => $this->getHost() . $item->getUrl(),
                    'name' => $this->xmlSpecialChars($item->category->name . ' ' . $item->brand->name),
                    'price' => $item->getPrice(),
                    'old_price' => $item->getOldPrice(),
                    'colors' => $this->getColors($item->colors),
                    'params' => $this->getOfferParams($item),
                    'category_id' => $item->category_id,
                    'pictures' => $media['images'],
                    'type_prefix' => $item->category->name,
                    'vendor' => $this->xmlSpecialChars($item->brand->name),
                    'model' => $this->xmlSpecialChars($item->sku),
                    'description' => $this->getDescription($item),
                    'sales_notes' => $this->getSalesNotes($item),
                    'video' => $media['videos'][0] ?? null,
                ];
            })->toArray();
    }

    /**
     * Prepare color from colors for filters
     */
    public function getColors(EloquentCollection $colors): array
    {
        return $colors->map(function (Color $color) {
            return $color->name;
        })->toArray();
    }

    /**
     * Prepare offer params
     */
    public function getOfferParams(Product $product): array
    {
        $params = [];
        if (!empty($product->fabric_top_txt)) {
            $name = 'Материал';
            if ($product->category->parent_id != Category::ACCESSORIES_PARENT_ID) {
                $name .= ' верха';
            }
            $params[$name] = $product->fabric_top_txt;
        }

        if (!empty($product->fabric_insole_txt)) {
            $params['Материал подкладки'] = $product->fabric_insole_txt;
        }

        if (!empty($product->fabric_outsole_txt)) {
            $params['Материал подошвы'] = $product->fabric_outsole_txt;
        }

        if (!empty($product->heel_txt)) {
            $params['Высота каблука'] = $product->heel_txt;
        }

        return $params;
    }

    /**
     * Retrieves the sales notes for a given product.
     *
     * @param  Product  $product  The product object.
     * @return string The sales notes for the product.
     */
    public function getSalesNotes(Product $product): string
    {
        return match (true) {
            ($product->category->parent_id == Category::ACCESSORIES_PARENT_ID) => 'Оплата при получении. Курьером или почтой.',
            ($product->getPrice() < 150) => 'Оплата при получении. Курьером или почтой. Примерка!',
            ($product->getPrice() >= 150) => 'Примерка. Оплата при получении. Рассрочка на 3 платежа!',
        };
    }
}
