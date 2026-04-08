<?php

namespace App\Http\Resources\Ads;

use App\Models\Ads\Banner;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @mixin Banner
 */
class BannerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isVideo = $this->type->isVideo();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'url' => $this->url,
            'type' => $this->type->value,
            'end_datetime' => $this->end_datetime,
            'show_timer' => $this->show_timer,
            'spoiler' => $this->spoiler,
            'media' => $this->media->mapWithKeys(function (Media $media) {
                return [$media->collection_name => $media->getFullUrl()];
            }),
        ];
    }
}
