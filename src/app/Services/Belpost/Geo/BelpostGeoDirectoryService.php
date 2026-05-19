<?php

namespace App\Services\Belpost\Geo;

use App\Libraries\Belpost\BelpostGeoDirectory;
use App\Models\Orders\Order;

/**
 * Extracts address fields from an {@see Order} and resolves them via {@see BelpostGeoDirectory}.
 */
class BelpostGeoDirectoryService
{
    public function __construct(
        private readonly BelpostGeoDirectory $geoDirectory,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function resolveForOrder(Order $order): array
    {
        $hints = $this->extractAddressHints($order);

        return $this->geoDirectory->resolve($hints, "Order #{$order->id}");
    }

    /**
     * Prefer structured `user.lastAddress`, fall back to order shipping fields / free-text `user_addr`.
     *
     * @return array{
     *     postcode: ?string,
     *     city: ?string,
     *     street: ?string,
     *     building: ?string,
     *     housing: ?string,
     *     region: ?string,
     *     district: ?string,
     * }
     */
    private function extractAddressHints(Order $order): array
    {
        $order->loadMissing('user.lastAddress');
        $address = $order->user?->lastAddress;

        $postcode = $this->geoDirectory->normalizePostcode($address?->zip ?? $order->zip);
        $city = $this->geoDirectory->normalizeCity($address?->city ?? $order->city);
        $street = $this->geoDirectory->normalizeStreetName(
            $address?->street
            ?? $this->geoDirectory->parseStreetFromText($order->user_addr)
            ?? $this->geoDirectory->parseStreetFromText($address?->address)
        );

        return [
            'postcode' => $postcode,
            'city' => $city,
            'street' => $street,
            'building' => $this->geoDirectory->normalizeBuilding($address?->house, $order->user_addr),
            'housing' => $this->geoDirectory->normalizeHousing($address?->corpus),
            'region' => $this->geoDirectory->normalizeRegion($address?->region ?? $order->region),
            'district' => $this->geoDirectory->normalizeDistrict($address?->district),
        ];
    }
}
