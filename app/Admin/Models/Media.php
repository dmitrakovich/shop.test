<?php

namespace App\Admin\Models;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\MediaLibrary\MediaCollections\Models\Media as MediaModel;

class Media extends MediaModel
{
    public function model(): MorphTo
    {
        return $this->morphTo()->withoutGlobalScope('publish');
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
