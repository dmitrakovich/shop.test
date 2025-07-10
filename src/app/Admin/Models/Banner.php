<?php

namespace App\Admin\Models;

use App\Models\Banner as BannerModel;
use Illuminate\Support\Facades\File;

/**
 * @property int $id
 * @property string|null $position
 * @property string|null $title
 * @property string|null $url
 * @property int $priority
 * @property bool $active
 * @property string|null $start_datetime
 * @property string|null $end_datetime
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property bool|null $show_timer
 * @property array|null $spoiler
 * @property mixed $resource
 * @property mixed $type
 * @property mixed $videos
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\MediaLibrary\MediaCollections\Models\Media[] $media
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Admin\Models\Banner active()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Admin\Models\Banner bannerFields()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Admin\Models\Banner orderByPriority()
 */
class Banner extends BannerModel
{
    protected $appends = [
        'resource',
        'type',
        'videos',
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
     * Resource mutator
     *
     * @param  string  $resource
     * @return \Spatie\MediaLibrary\MediaCollections\Models\Media
     */
    public function setResourceAttribute($resource)
    {
        return $this->clearMediaCollection()
            ->addMedia(public_path("uploads/$resource"))
            ->toMediaCollection();
    }

    /**
     * Resource accessor
     *
     * @return string
     */
    public function getResourceAttribute()
    {
        return $this->getFirstMediaUrl();
    }

    /**
     * Interact with the banner's type.
     */
    public function getTypeAttribute()
    {
        return intval(optional($this->getMedia()->first())->hasCustomProperty('videos'));
    }

    /**
     * Video mutator
     *
     * @return void
     */
    public function setVideosAttribute(array $videos)
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

    /**
     * Video accessor
     *
     * @return array
     */
    public function getVideosAttribute()
    {
        $videos = optional($this->getMedia()->first())->getCustomProperty('videos');

        if (empty($videos) || !is_array($videos)) {
            return null;
        }

        return array_map(fn ($video) => "files/$video", $videos);
    }
}
