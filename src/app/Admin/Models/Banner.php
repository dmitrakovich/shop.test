<?php

namespace App\Admin\Models;

use Illuminate\Support\Facades\File;
use App\Models\Banner as BannerModel;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Banner extends BannerModel
{
    protected $appends = [
        'resource',
        'videos',
        'type',
    ];
    protected static $availablesVideoTypes = [
        'video/mp4',
        'video/webm',
        'video/ogg',
    ];

    final const ERRORS = [
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

    /**
     * Interact with the banner's resource.
     */
    public function path(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getFirstMediaUrl(),
            set: fn ($resource) => $this->clearMediaCollection()
                ->addMedia(public_path("uploads/$resource"))
                ->toMediaCollection()
        );
    }

    /**
     * Interact with the banner's type.
     */
    public function type(): Attribute
    {
        return Attribute::make(
            get: fn () => intval(optional($this->getMedia()->first())->hasCustomProperty('videos'))
        );
    }

    /**
     * Interact with the banner's videos.
     */
    public function videos(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getVideos(),
            set: fn ($videos) => $this->setVideos($videos)
        );
    }

    public function setVideos(array $videos)
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

    public function getVideos()
    {
        $videos = optional($this->getMedia()->first())->getCustomProperty('videos');

        if (empty($videos) || !is_array($videos)) {
            return null;
        }

        return array_map(fn ($video) => "files/$video", $videos);
    }
}
