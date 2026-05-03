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
}
