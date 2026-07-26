<?php

namespace App\Providers;

use App\ElasticSearch\Indices\Product\Aggregations\AggregationBuilder;
use App\ElasticSearch\Indices\Product\Aggregations\Builders\BrandAggregationBuilder;
use App\ElasticSearch\Indices\Product\Aggregations\Builders\CategoryAggregationBuilder;
use App\ElasticSearch\Indices\Product\Aggregations\Builders\CollectionAggregationBuilder;
use App\ElasticSearch\Indices\Product\Aggregations\Builders\ColorAggregationBuilder;
use App\ElasticSearch\Indices\Product\Aggregations\Builders\FabricAggregationBuilder;
use App\ElasticSearch\Indices\Product\Aggregations\Builders\HeelAggregationBuilder;
use App\ElasticSearch\Indices\Product\Aggregations\Builders\PriceStatsAggregationBuilder;
use App\ElasticSearch\Indices\Product\Aggregations\Builders\SeasonAggregationBuilder;
use App\ElasticSearch\Indices\Product\Aggregations\Builders\SizeAggregationBuilder;
use App\ElasticSearch\Indices\Product\Aggregations\Builders\StatusAggregationBuilder;
use App\ElasticSearch\Indices\Product\Aggregations\Builders\StyleAggregationBuilder;
use App\ElasticSearch\Indices\Product\Aggregations\Builders\TagAggregationBuilder;
use App\ElasticSearch\Indices\Product\Filters\DefaultProductFilter;
use App\ElasticSearch\Indices\Product\ProductDocument;
use App\ElasticSearch\Indices\Product\ProductIndex;
use App\ElasticSearch\Indices\Product\ProductIndexWriter;
use App\ElasticSearch\Indices\Product\Queries\CatalogQuery;
use App\ElasticSearch\Indices\Product\Search\DefaultProductSearch;
use App\ElasticSearch\Indices\Product\Transformer\DefaultProductTransformer;
use Illuminate\Support\ServiceProvider;

class ElasticSearchServiceProvider extends ServiceProvider
{
    /**
     * Register Elasticsearch catalog bindings.
     */
    public function register(): void
    {
        $this->app->singleton(AggregationBuilder::class, function () {
            return new AggregationBuilder(
                new CategoryAggregationBuilder,
                new BrandAggregationBuilder,
                new SizeAggregationBuilder,
                new ColorAggregationBuilder,
                new FabricAggregationBuilder,
                new HeelAggregationBuilder,
                new SeasonAggregationBuilder,
                new StyleAggregationBuilder,
                new TagAggregationBuilder,
                new CollectionAggregationBuilder,
                new StatusAggregationBuilder,
                new PriceStatsAggregationBuilder,
            );
        });

        $this->app->bind(CatalogQuery::class, function ($app) {
            return new CatalogQuery(
                $app->make(DefaultProductSearch::class),
                $app->make(DefaultProductFilter::class),
                $app->make(DefaultProductTransformer::class),
                $app->make(AggregationBuilder::class)
            );
        });

        $this->app->singleton(DefaultProductSearch::class);
        $this->app->singleton(DefaultProductFilter::class);
        $this->app->singleton(DefaultProductTransformer::class);

        $this->app->singleton(ProductIndex::class);
        $this->app->singleton(ProductIndexWriter::class);
        $this->app->singleton(ProductDocument::class);
    }

    /**
     * Bootstrap Elasticsearch provider.
     */
    public function boot(): void
    {
        //
    }
}
