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
            'full_ext' => substr($this->file_name, 5),
            // 'thumb_url' => $this->getUrl('thumb'),
            // 'video_url' => $this->getCustomProperty('video'),
        ];
    }
}
