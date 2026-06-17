<?php

namespace App\Services\Media;

use FFMpeg\FFMpeg;
use FFMpeg\Format\Video\X264;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Spatie\MediaLibrary\Conversions\FileManipulator;
use Spatie\MediaLibrary\MediaCollections\Filesystem;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\TemporaryDirectory;

/**
 * Converts uploaded videos (MOV, AVI, etc.) to web-compatible MP4 (H.264 + AAC).
 *
 * Files are stored on S3, so ffmpeg works on a local temp copy rather than Media::getPath().
 */
class VideoConversionService
{
    /** Matches App\Models\Media\FileNamer::originalFileName(). */
    private const string MP4_FILE_NAME = 'full.mp4';

    private const string MP4_MIME_TYPE = 'video/mp4';

    public function __construct(
        private readonly Filesystem $mediaFilesystem,
        private readonly FileManipulator $fileManipulator,
    ) {}

    public function isMp4(Media $media): bool
    {
        return str_ends_with(strtolower($media->file_name), '.mp4');
    }

    /**
     * @return bool True when conversion ran or DB was synced to an existing MP4, false when already MP4.
     */
    public function convertToMp4(Media $media): bool
    {
        if ($this->isMp4($media)) {
            return false;
        }

        $originalRelativePath = $media->getPathRelativeToRoot();
        $mp4RelativePath = $this->mp4RelativePath($media);

        if ($this->diskExists($media, $mp4RelativePath)) {
            $this->finalizeMp4($media, $mp4RelativePath, $originalRelativePath);

            return true;
        }

        if (!$this->diskExists($media, $originalRelativePath)) {
            throw new RuntimeException(sprintf(
                'Source video not found on disk for media #%d: %s',
                $media->id,
                $originalRelativePath,
            ));
        }

        $temporaryDirectory = TemporaryDirectory::create();

        try {
            $inputExtension = pathinfo($media->file_name, PATHINFO_EXTENSION) ?: 'bin';
            $inputPath = $temporaryDirectory->path('input.' . $inputExtension);
            $outputPath = $temporaryDirectory->path('output.mp4');

            // Download from remote disk — getPath() is not usable for S3.
            $this->mediaFilesystem->copyFromMediaLibrary($media, $inputPath);
            $this->convertFile($inputPath, $outputPath);

            $this->mediaFilesystem->copyToMediaLibrary(
                $outputPath,
                $media,
                null,
                self::MP4_FILE_NAME,
            );

            $this->finalizeMp4(
                $media,
                $mp4RelativePath,
                $originalRelativePath,
                filesize($outputPath) ?: $media->size,
            );

            return true;
        } catch (\Throwable $exception) {
            Log::error('Video conversion to MP4 failed', [
                'media_id' => $media->id,
                'file_name' => $media->file_name,
                'collection' => $media->collection_name,
                'exception' => $exception,
            ]);

            throw $exception;
        } finally {
            $temporaryDirectory->delete();
        }
    }

    private function finalizeMp4(
        Media $media,
        string $mp4RelativePath,
        ?string $originalRelativePath,
        ?int $size = null,
    ): void {
        $disk = Storage::disk($media->disk);

        if (
            $originalRelativePath !== null
            && $originalRelativePath !== $mp4RelativePath
            && $this->diskExists($media, $originalRelativePath)
        ) {
            $this->mediaFilesystem->removeFile($media, $originalRelativePath);
        }

        // Drop thumbs generated from the source file while conversion flags are still present.
        $this->removeGeneratedConversions($media);

        // Custom PathGenerator stores files as "{path}-{file_name}" (no slash). Spatie's
        // MediaObserver would try "{path}/{file_name}" on file_name change, so skip events.
        $media->updateQuietly([
            'file_name' => self::MP4_FILE_NAME,
            'mime_type' => self::MP4_MIME_TYPE,
            'size' => $size ?? ($disk->exists($mp4RelativePath) ? $disk->size($mp4RelativePath) : $media->size),
            'generated_conversions' => [],
        ]);

        $media->refresh();

        // Rebuild thumb etc. from the new MP4 (may run in the media queue).
        $this->fileManipulator->createDerivedFiles(
            $media->fresh(),
            onlyMissing: false,
            queueAll: (bool)config('media-library.queue_conversions_by_default', true),
        );
    }

    private function mp4RelativePath(Media $media): string
    {
        return $this->mediaFilesystem->getMediaDirectory($media) . self::MP4_FILE_NAME;
    }

    private function diskExists(Media $media, string $relativePath): bool
    {
        return Storage::disk($media->disk)->exists($relativePath);
    }

    private function convertFile(string $inputPath, string $outputPath): void
    {
        $ffmpeg = FFMpeg::create([
            'ffmpeg.binaries' => config('media-library.ffmpeg_path'),
            'ffprobe.binaries' => config('media-library.ffprobe_path'),
            'timeout' => 3600,
        ]);

        $format = new X264('aac');
        // Move moov atom to the start so playback can begin before full download (HTML5 video).
        $format->setAdditionalParameters(['-movflags', '+faststart']);

        $ffmpeg->open($inputPath)->save($format, $outputPath);
    }

    /**
     * Uses Filesystem::removeFile() because FileRemover interface does not expose per-conversion cleanup.
     */
    private function removeGeneratedConversions(Media $media): void
    {
        foreach (array_keys($media->generated_conversions ?? []) as $conversionName) {
            if (!$media->hasGeneratedConversion($conversionName)) {
                continue;
            }

            $this->mediaFilesystem->removeFile(
                $media,
                $media->getPathRelativeToRoot($conversionName),
            );
        }
    }
}
