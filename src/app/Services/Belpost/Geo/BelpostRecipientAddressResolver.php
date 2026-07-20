<?php

namespace App\Services\Belpost\Geo;

use App\Models\Orders\Order;

/**
 * Resolves the `address` object for Belpost recipient payloads (geo-directory format).
 */
class BelpostRecipientAddressResolver
{
    public function __construct(
        private readonly BelpostGeoDirectoryService $geoDirectory,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function resolve(Order $order): array
    {
        if (config('belpost.geo_directory.enabled', true)) {
            return $this->geoDirectory->resolveForOrder($order);
        }

        throw new \RuntimeException('Belpost geo directory is required for address resolution.');
    }
}
