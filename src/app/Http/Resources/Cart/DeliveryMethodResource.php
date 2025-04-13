<?php

namespace App\Http\Resources\Cart;

use Deliveries\DeliveryMethod;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin DeliveryMethod
 */
class DeliveryMethodResource extends JsonResource
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
            'info_data' => $this->instance->getAdditionalInfo()?->getData(),
            'info_html' => $this->instance->getAdditionalInfo()?->render(),
        ];
    }
}
