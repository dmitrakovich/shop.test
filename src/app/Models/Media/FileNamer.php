<?php

namespace App\Models\Media;

use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\Support\FileNamer\DefaultFileNamer;

class FileNamer extends DefaultFileNamer
{
    public function originalFileName(string $fileName): string
    {
        return 'full';
    }

    public function conversionFileName(string $fileName, Conversion $conversion): string
    {
        return str_replace(['-jpg', '-webp'], '', $conversion->getName());
    }
}
