<?php

namespace App\Http\Resources\Ads;

use App\Enums\Ads\BannerMediaCollection;
use App\Models\Ads\Banner;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'media' => [
                'desktop' => $isVideo
                    ? $this->getFirstMediaUrl(BannerMediaCollection::DESKTOP_VIDEO->value)
                    : $this->getFirstMediaUrl(BannerMediaCollection::DESKTOP_IMAGE->value),
                'mobile' => $isVideo
                    ? $this->getFirstMediaUrl(BannerMediaCollection::MOBILE_VIDEO->value)
                    : $this->getFirstMediaUrl(BannerMediaCollection::MOBILE_IMAGE->value),
                'desktop_preview' => $isVideo
                    ? $this->getFirstMediaUrl(BannerMediaCollection::DESKTOP_VIDEO_PREVIEW->value)
                    : null,
                'mobile_preview' => $isVideo
                    ? $this->getFirstMediaUrl(BannerMediaCollection::MOBILE_VIDEO_PREVIEW->value)
                    : null,
            ],
        ];
    }
}
