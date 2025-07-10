<?php

namespace App\Http\Resources\Info;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Stock
 */
class ShopResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address,
            'worktime' => $this->worktime,
            'phone' => $this->phone,
            'geo_latitude' => $this->geo_latitude,
            'geo_longitude' => $this->geo_longitude,
            'photos' => $this->getPhotos(),
        ];
    }
}
