<?php

namespace App\Rules;

use Illuminate\Validation\Rules\File;

class VideoFile extends File
{
    /**
     * The MIME types that the given file should match. This array may also contain file extensions.
     *
     * @var array<int, string>
     */
    protected $allowedMimetypes = [
        'video/mp4',
        'video/avi',
        'video/mpeg',
        'video/quicktime',
    ];
}
