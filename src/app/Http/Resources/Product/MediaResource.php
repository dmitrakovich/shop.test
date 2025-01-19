<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \Spatie\MediaLibrary\MediaCollections\Models\Media
 */
class MediaResource extends JsonResource
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
            'thumb_url' => $this->getUrl('thumb'),
            'catalog_url' => $this->getUrl('catalog'),
            'normal_url' => $this->getUrl('normal'),
            'full_url' => $this->getUrl('full'),
            'original_url' => $this->getFullUrl(),
        ];
    }
}
