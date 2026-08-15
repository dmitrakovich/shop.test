<?php

namespace Tests\Unit\Elastic;

use App\Enums\Catalog\CatalogFacetName;
use App\Models\Brand;
use App\Models\ProductAttributes\Status;
use App\Models\Size;
use PHPUnit\Framework\TestCase;

class CatalogFacetNameTest extends TestCase
{
    public function test_each_facet_exposes_a_non_null_elastic_field(): void
    {
        foreach (CatalogFacetName::cases() as $facet) {
            $this->assertNotSame('', $facet->field());
            $this->assertSame($facet->model()::elasticField(), $facet->field());
        }
    }

    public function test_brands_map_to_brand_id_field(): void
    {
        $this->assertSame(Brand::class, CatalogFacetName::Brands->model());
        $this->assertSame('brand.id', CatalogFacetName::Brands->field());
    }

    public function test_statuses_aggregate_by_slug(): void
    {
        $this->assertSame(Status::class, CatalogFacetName::Statuses->model());
        $this->assertSame('slug', Status::elasticFacetKey());
        $this->assertSame('status_slugs', CatalogFacetName::Statuses->field());
    }

    public function test_sizes_include_insole_and_active_where(): void
    {
        $this->assertSame(['insole'], Size::elasticFacetExtras());
        $this->assertSame(['is_active' => true], Size::elasticFacetWhere());
    }
}
