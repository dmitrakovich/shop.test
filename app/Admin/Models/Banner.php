<?php

namespace App\Admin\Models;

use App\Models\Banner as BannerModel;
use Illuminate\Support\Facades\File;

class Banner extends BannerModel
{
    protected $appends = [
        'resource',
        'videos',
        'type',
    ];
    protected static array $availablesVideoTypes = [
        'video/mp4',
        'video/webm',
        'video/ogg',
    ];

    const ERRORS = [
        'empty_preview' => 'Превью для видео не может быть пустым!',
    ];
    /**
     * Get the class name for polymorphic relations.
     *
     * @return string
     */
    public function getMorphClass()
    {
        return BannerModel::class;
    }

    public function setResourceAttribute($resource)
    {
        $this->clearMediaCollection()
            ->addMedia(public_path("uploads/$resource"))
            ->toMediaCollection();
    }

    public function getResourceAttribute()
    {
        return $this->getFirstMediaUrl();
    }

    public function getTypeAttribute()
    {
        return intval(optional($this->getMedia()->first())->hasCustomProperty('videos'));
    }

    public function setVideosAttribute($videos)
    {
        if ($this->getMedia()->isEmpty()) {
            admin_error(self::ERRORS['empty_preview']);
            throw new \Exception(self::ERRORS['empty_preview']);
        }
        $mediaModel = $this->getMedia()->first();
        foreach ($videos as $video) {
            $type = File::mimeType(public_path("uploads/$video"));
            if (in_array($type, self::$availablesVideoTypes)) {
                $attachVideos[$type] = basename($video);
            }
        }
        if (empty($attachVideos)) {
            $mediaModel->forgetCustomProperty('videos');
        } else {
            $mediaModel->setCustomProperty('videos', $attachVideos);
        }
        if (isset($mediaModel->model_type)) {
            $mediaModel->save();
        }
    }

    public function getVideosAttribute()
    {
        $videos = optional($this->getMedia()->first())->getCustomProperty('videos');

        if (empty($videos) || !is_array($videos)) {
            return null;
        }

        return array_map(function($video) {
            return "files/$video";
        }, $videos);
    }
}
