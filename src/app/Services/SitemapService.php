<?php

namespace App\Services;

use App\Helpers\UrlHelper;
use App\Models\City;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;
use Illuminate\Support\Facades\File;

class SitemapService
{
    private string $lastmod;

    private int $totalUrlcount = 0;

    private array $resultFiles = [];

    /**
     * Get simple product slider
     */
    public function generate(): bool
    {
        $start = microtime(true);
        $this->lastmod = date('Y-m-d');
        $path = config('sitemap.path');
        $sitemapFiles = config('sitemap.files');

        foreach ($sitemapFiles as $sitemapFilesKey => $sitemapFile) {
            $sitemapLimit = $sitemapFile['limit'] ?? 99999;
            $sitemapPart = 0;
            switch ($sitemapFilesKey) {
                case 'products':
                    Product::withTrashed()
                        ->with('category')
                        ->select('id', 'slug', 'category_id')
                        ->orderBy('id', 'asc')
                        ->chunk($sitemapLimit, function ($products) use ($path, $sitemapFile, &$sitemapPart) {
                            $tempPath = $path . '/' . uniqid() . '.xml';
                            $finalPath = $path . '/' . $sitemapFile['file_name'] . ($sitemapPart ? ('-' . $sitemapPart) : '') . '.xml';
                            $this->urlsetFileStart($tempPath);
                            foreach ($products as $product) {
                                $url = url($product->category->getUrl() . '/' . $product->slug);
                                $data = $this->getUrl($url, $sitemapFile['changefreq'], $sitemapFile['priority']);
                                $this->urlsetAppendFile($tempPath, $data);
                            }
                            $this->urlsetFileEnd($tempPath, $finalPath);
                            $sitemapPart++;
                        });
                    break;
                case 'static':
                    $tempPath = $path . '/' . uniqid() . '.xml';
                    $finalPath = $path . '/' . $sitemapFile['file_name'] . '.xml';
                    $this->urlsetFileStart($tempPath);
                    foreach ($sitemapFile['routes'] as $route) {
                        $url = route($route, [], true);
                        $data = $this->getUrl($url, $sitemapFile['changefreq'], $sitemapFile['priority']);
                        $this->urlsetAppendFile($tempPath, $data);
                    }
                    foreach ($sitemapFile['routesWithParams'] as $route => $params) {
                        foreach ($params as $param) {
                            $url = route($route, $param, true);
                            $data = $this->getUrl($url, $sitemapFile['changefreq'], $sitemapFile['priority']);
                            $this->urlsetAppendFile($tempPath, $data);
                        }
                    }
                    $this->urlsetFileEnd($tempPath, $finalPath);
                    break;
                case 'catalog':
                    $fileName = (string)($sitemapFile['file_name'] ?? 'sitemap.catalog');
                    $optionList = (isset($sitemapFile['options_list']) && is_array($sitemapFile['options_list'])) ? $sitemapFile['options_list'] : [];
                    foreach ($optionList as $optionItem) {
                        $tempPath = $path . '/' . uniqid() . '.xml';
                        $this->urlsetFileStart($tempPath);

                        $priority = (float)($optionItem['priority'] ?? $sitemapFile['priority'] ?? 1);
                        $changefreq = (string)($optionItem['changefreq'] ?? $sitemapFile['changefreq'] ?? 'daily');
                        $limit = (int)($optionItem['limit'] ?? $sitemapFile['limit'] ?? 5000);
                        $optionValues = (isset($optionItem['values']) && is_array($optionItem['values'])) ? $optionItem['values'] : [];
                        $citiesExsist = false;

                        $relations = [];
                        $productModel = new Product;
                        foreach ($optionValues as $optionValue) {
                            $relation = $this->checkProductRelation($productModel, $optionValue);
                            if ($optionValue === 'cities') {
                                $citiesExsist = true;
                            }
                            if ($relation) {
                                $relations[get_class($relation->getModel())] = $optionValue;
                            }
                        }

                        $canonicalOrder = UrlHelper::CANONICAL_ORDER;
                        uksort($relations, function ($val1, $val2) use ($canonicalOrder) {
                            return array_search($val1, $canonicalOrder) > array_search($val2, $canonicalOrder);
                        });

                        if ($citiesExsist) {
                            $finalPath = $path . '/' . $fileName . '.' . implode('_and_', ['cities', ...$relations]) . '.xml';
                        } else {
                            $finalPath = $path . '/' . $fileName . '.' . implode('_and_', $relations) . '.xml';
                        }

                        $cityList = $citiesExsist ? City::pluck('slug', 'id')->toArray() : [];
                        $productsQuery = (new Product())->newQuery();
                        $productsQuery = $productsQuery->select('id', 'label_id', 'category_id', 'season_id', 'brand_id', 'manufacturer_id', 'collection_id');
                        foreach ($relations as $relation) {
                            $productsQuery = $productsQuery->whereHas($relation)->with($relation);
                        }

                        $attributeLinks = [];
                        $productsQuery->chunk($limit, function ($products) use (&$attributeLinks, $relations, $cityList) {
                            foreach ($products as $product) {
                                $optionsGroup = [];
                                foreach ($relations as $relation) {
                                    $productRelation = $product->{$relation};
                                    if ($productRelation instanceof Collection) {
                                        foreach ($productRelation as $productRelationItem) {
                                            $optionsGroup[get_class($productRelationItem)][$productRelationItem->id] = $productRelationItem->slug;
                                        }
                                    } else {
                                        $optionsGroup[get_class($productRelation)][$productRelation->id] = $productRelation->slug;
                                    }
                                }
                                if (!empty($cityList)) {
                                    $optionsGroup = ['cities' => $cityList] + $optionsGroup;
                                }
                                $links = $this->getAttributeLinks($optionsGroup);
                                foreach ($links as $link) {
                                    $attributeLinks[$link] = true;
                                }
                            }
                        });

                        foreach ($attributeLinks as $attributeLink => $exsist) {
                            $url = route('shop', $attributeLink, true);
                            $data = $this->getUrl($url, $changefreq, $priority);
                            $this->urlsetAppendFile($tempPath, $data);
                        }
                        $this->urlsetFileEnd($tempPath, $finalPath);
                    }
                    break;
            }
        }

        $finalPath = $path . '/' . config('sitemap.index_name') . '.xml';
        $resultData = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $resultData .= "<sitemapindex xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
        File::ensureDirectoryExists(dirname($finalPath));
        foreach ($this->resultFiles as $resultFile) {
            $resultUrl = secure_url(basename($resultFile));
            $resultData .= <<<URL
        <sitemap>
          <loc>$resultUrl</loc>
          <lastmod>$this->lastmod</lastmod>
        </sitemap>
      URL . PHP_EOL;
        }
        $resultData .= "</sitemapindex>\n";
        File::put($finalPath, $resultData);

        return true;
    }

    /**
     * Check if model is related
     *
     * @param  Model  $models
     * @param  string  $relation
     */
    private function checkProductRelation(Model $productModel, string $attribute): ?Relations\Relation
    {
        if (
            method_exists($productModel, $attribute) &&
            !method_exists(Model::class, $attribute)
        ) {
            $relation = call_user_func([$productModel, $attribute]);
            if ($relation instanceof Relations\Relation) {
                return $relation;
            }
        }

        return null;
    }

    /**
     * Get catalog attribute links
     */
    private function getAttributeLinks(array $models): array
    {
        $result = [];
        foreach (array_shift($models) as $attrKey => $attr) {
            if (!empty($models)) {
                foreach ($this->getAttributeLinks($models, false) as $attributeLink) {
                    $result[] = implode('/', [$attr, $attributeLink]);
                }
            } else {
                $result[] = $attr;
            }
        }

        return $result;
    }

    /**
     * Get sitemap Url
     */
    private function getUrl(string $loc, string $changefreq, float $priority = 0.7): string
    {
        $url = url($loc);

        return <<<URL
            <url>
                <loc>$url</loc>
                <lastmod>$this->lastmod</lastmod>
                <changefreq>$changefreq</changefreq>
                <priority>$priority</priority>
            </url>
        URL . PHP_EOL;
    }

    /**
     * Footer sitemap and move to final path
     *
     * @param  string  $tempPath - temp file path
     * @param  string  $finalPath - final file path
     */
    private function urlsetFileEnd(string $tempPath, string $finalPath): void
    {
        $data = '</urlset>';
        File::append($tempPath, $data);
        File::ensureDirectoryExists(dirname($finalPath));
        File::move($tempPath, $finalPath);
        $this->resultFiles[] = $finalPath;
    }

    /**
     * Header sitemap
     *
     * @param  string  $tempPath - temp file path
     */
    private function urlsetFileStart(string $tempPath): void
    {
        $data = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $data .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
        File::ensureDirectoryExists(dirname($tempPath));
        File::put($tempPath, $data);
    }

    /**
     * Add row to sitemap
     *
     * @param  string  $tempPath - temp file path
     */
    private function urlsetAppendFile(string $tempPath, string $data): void
    {
        File::append($tempPath, $data);
        $this->totalUrlcount++;
    }
}
