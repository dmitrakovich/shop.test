<?php

namespace Tests\Unit\Elastic;

use App\Enums\Catalog\CatalogFacetName;
use App\Models\Brand;
use App\Models\ProductAttributes\Status;
use App\Models\Size;
use PHPUnit\Framework\TestCase;

class CatalogFacetNameTest extends TestCase
{
    public function test_each_facet_maps_to_a_filterable_model_with_an_elastic_field(): void
    {
        foreach (CatalogFacetName::cases() as $facet) {
            $model = $facet->model();

            $this->assertNotNull(
                $model::elasticField(),
                "{$facet->value} model must define elasticField()",
            );
        }
    }

    public function test_brands_map_to_brand_id_field(): void
    {
        $this->assertSame(Brand::class, CatalogFacetName::Brands->model());
        $this->assertSame('brand.id', Brand::elasticField());
    }

    public function test_statuses_aggregate_by_slug(): void
    {
        $this->assertSame(Status::class, CatalogFacetName::Statuses->model());
        $this->assertSame('slug', Status::elasticFacetKey());
    }

    public function test_sizes_include_insole_and_active_where(): void
    {
        $this->assertSame(['insole'], Size::elasticFacetExtras());
        $this->assertSame(['is_active' => true], Size::elasticFacetWhere());
    }
}
