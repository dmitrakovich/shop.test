<?php

namespace App\Admin\Models;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\MediaLibrary\MediaCollections\Models\Media as MediaModel;

/**
 * @property int $id
 * @property string $model_type
 * @property int $model_id
 * @property string|null $uuid
 * @property string $collection_name
 * @property string $name
 * @property string $file_name
 * @property string|null $mime_type
 * @property string $disk
 * @property string|null $conversions_disk
 * @property int $size
 * @property array $manipulations
 * @property array $custom_properties
 * @property array $generated_conversions
 * @property array $responsive_images
 * @property int|null $order_column
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property mixed $type
 * @property mixed $extension
 * @property mixed $humanReadableSize
 * @property mixed $human_readable_size
 * @property mixed $previewUrl
 * @property mixed $preview_url
 * @property mixed $originalUrl
 * @property mixed $original_url
 *
 * @property-read \Illuminate\Database\Eloquent\Model|null $model
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Admin\Models\Media ordered()
 */
class Media extends MediaModel
{
    public function model(): MorphTo
    {
        return $this->morphTo()->withTrashed();
    }

    /**
     * Get the class name for polymorphic relations.
     *
     * @return string
     */
    public function getMorphClass()
    {
        return MediaModel::class;
    }
}
