<?php

namespace App\Enums\Ads;

enum BannerMediaCollection: string
{
    case DESKTOP_IMAGE = 'desktop_image';
    case MOBILE_IMAGE = 'mobile_image';
    case DESKTOP_VIDEO = 'desktop_video';
    case MOBILE_VIDEO = 'mobile_video';
    case DESKTOP_VIDEO_PREVIEW = 'desktop_video_preview';
    case MOBILE_VIDEO_PREVIEW = 'mobile_video_preview';

    public function isDesktop(): bool
    {
        return str_starts_with($this->value, 'desktop_');
    }

    public function isImage(): bool
    {
        return in_array($this, self::images(), true);
    }

    /**
     * @return list<self>
     */
    public static function images(): array
    {
        return [
            self::DESKTOP_IMAGE,
            self::MOBILE_IMAGE,
            self::DESKTOP_VIDEO_PREVIEW,
            self::MOBILE_VIDEO_PREVIEW,
        ];
    }

    /**
     * @return list<self>
     */
    public static function videos(): array
    {
        return [
            self::DESKTOP_VIDEO,
            self::MOBILE_VIDEO,
        ];
    }
}
