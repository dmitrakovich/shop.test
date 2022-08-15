<?php

namespace App\Facades;

use App\Services\Seo\MetaService;
use App\Services\Seo\TitleGenerotorService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static string getProductTitle() Return generated title for product page
 * @method static string getProductDescription() Return generated description for product page
 * @method static string getCatalogTitle() Return generated title for catalog page
 * @method static string getCatalogDescription() Return generated description for catalog page
 */
class Seo extends Facade
{
    /**
     * Get the instance object
     */
    public static function getFacadeInstance(string $method): mixed
    {
        $instance = match ($method) {
            'getProductTitle', 'getProductDescription', 'getCatalogTitle', 'getCatalogDescription' => TitleGenerotorService::class,
            'metaForRobotsForCatalog' => MetaService::class,
        };

        return static::resolveFacadeInstance($instance);
    }

    /**
     * Handle dynamic, static calls to the object.
     *
     * @param  string  $method
     * @param  array  $args
     * @return mixed
     *
     * @throws \RuntimeException
     */
    public static function __callStatic($method, $args)
    {
        $instance = static::getFacadeInstance($method);

        if (!$instance) {
            throw new \RuntimeException('A facade root has not been set.');
        }

        return $instance->$method(...$args);
    }
}
