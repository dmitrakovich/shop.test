<?php

namespace Tests\Unit\Elastic;

use App\Enums\Catalog\CatalogFacetName;
use App\Models\Brand;
use App\Services\Elasticsearch\CatalogFacetConfig;
use App\Services\Elasticsearch\CatalogFacetDefinition;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class CatalogFacetDefinitionTest extends TestCase
{
    public function test_it_defines_one_readonly_config_for_each_facet_name(): void
    {
        $configs = CatalogFacetDefinition::all();

        $this->assertSame(
            CatalogFacetName::cases(),
            array_map(
                static fn (CatalogFacetConfig $config): CatalogFacetName => $config->name,
                $configs,
            ),
        );

        foreach ((new ReflectionClass(CatalogFacetConfig::class))->getProperties() as $property) {
            $this->assertTrue($property->isReadOnly());
        }
    }

    public function test_it_finds_a_typed_config_by_model(): void
    {
        $config = CatalogFacetDefinition::forModel(Brand::class);

        $this->assertInstanceOf(CatalogFacetConfig::class, $config);
        $this->assertSame(CatalogFacetName::Brands, $config->name);
        $this->assertSame('brand.id', $config->field);
    }
}
