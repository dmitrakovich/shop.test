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
     *     apartment: ?string,
     *     region: ?string,
     *     district: ?string,
     * }
     */
    private function extractAddressHints(Order $order): array
    {
        $order->loadMissing('user.lastAddress');
        $address = $order->user?->lastAddress;

        $postcode = $this->geoDirectory->normalizePostcode(
            $address !== null ? ($address->zip ?? $order->zip) : $order->zip,
        );
        $city = $this->geoDirectory->normalizeCity(
            $address !== null ? ($address->city ?? $order->city) : $order->city,
        );
        $street = $this->geoDirectory->normalizeStreetName(
            ($address !== null ? $address->street : null)
            ?? $this->geoDirectory->parseStreetFromText($order->user_addr)
            ?? $this->geoDirectory->parseStreetFromText($address !== null ? $address->address : null)
        );

        return [
            'postcode' => $postcode,
            'city' => $city,
            'street' => $street,
            'building' => $this->geoDirectory->normalizeBuilding($address !== null ? $address->house : null, $order->user_addr),
            'housing' => $this->geoDirectory->normalizeHousing($address !== null ? $address->corpus : null),
            'apartment' => $this->geoDirectory->normalizeApartment($address !== null ? $address->room : null, $order->user_addr),
            'region' => $this->geoDirectory->normalizeRegion(($address !== null ? $address->region : null) ?? $order->region),
            'district' => $this->geoDirectory->normalizeDistrict($address !== null ? $address->district : null),
        ];
    }
}
