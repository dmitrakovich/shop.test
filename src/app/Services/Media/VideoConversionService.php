<?php

namespace App\Services\Media;

use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use FFMpeg\Format\Video\X264;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Spatie\MediaLibrary\Conversions\FileManipulator;
use Spatie\MediaLibrary\MediaCollections\Filesystem;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\TemporaryDirectory;

/**
 * Converts uploaded videos (MOV, HEVC MP4, AVI, etc.) to web-compatible MP4 (H.264 + AAC).
 *
 * Files are stored on S3, so ffmpeg works on a local temp copy rather than Media::getPath().
 */
class VideoConversionService
{
    /** Matches App\Models\Media\FileNamer::originalFileName(). */
    private const string MP4_FILE_NAME = 'full.mp4';

    private const string MP4_MIME_TYPE = 'video/mp4';

    /** Video codecs supported by Chrome / Firefox / Edge in an MP4 container. */
    private const array WEB_COMPATIBLE_VIDEO_CODECS = ['h264'];

    /** Audio codecs supported alongside H.264 in HTML5 video. */
    private const array WEB_COMPATIBLE_AUDIO_CODECS = ['aac'];

    public function __construct(
        private readonly Filesystem $mediaFilesystem,
        private readonly FileManipulator $fileManipulator,
    ) {}

    /**
     * @return bool True when conversion ran or DB was synced to an existing MP4, false when already web-compatible.
     */
    public function convertToMp4(Media $media): bool
    {
        $originalRelativePath = $media->getPathRelativeToRoot();
        $mp4RelativePath = $this->mp4RelativePath($media);

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

            $webCompatible = $this->isWebCompatible($inputPath);

            // Already H.264 (+ AAC) and DB/path already point at full.mp4 — nothing to do.
            // HEVC-in-MP4 fails isWebCompatible and is re-encoded below (not skipped by extension).
            if ($webCompatible && $this->isNormalizedMp4Record($media, $originalRelativePath, $mp4RelativePath)) {
                return false;
            }

            if ($webCompatible) {
                $this->remuxWithFaststart($inputPath, $outputPath);
            } else {
                $this->convertFile($inputPath, $outputPath);
            }

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

    private function isNormalizedMp4Record(
        Media $media,
        string $originalRelativePath,
        string $mp4RelativePath,
    ): bool {
        return $media->file_name === self::MP4_FILE_NAME
            && $media->mime_type === self::MP4_MIME_TYPE
            && $originalRelativePath === $mp4RelativePath;
    }

    private function isWebCompatible(string $filePath): bool
    {
        $ffprobe = $this->createFfprobe();
        $streams = $ffprobe->streams($filePath);

        $videoStream = $streams->videos()->first();
        if ($videoStream === null) {
            return false;
        }

        $videoCodec = (string)$videoStream->get('codec_name');
        $audioStream = $streams->audios()->first();
        $audioCodec = $audioStream !== null ? (string)$audioStream->get('codec_name') : null;

        return $this->areCodecsWebCompatible($videoCodec, $audioCodec);
    }

    /**
     * Chrome / Firefox / Edge play MP4 only with H.264 video and AAC audio (or no audio).
     * HEVC (hevc/h265), ProRes, etc. must be re-encoded.
     */
    public function areCodecsWebCompatible(string $videoCodec, ?string $audioCodec = null): bool
    {
        if (!in_array(strtolower($videoCodec), self::WEB_COMPATIBLE_VIDEO_CODECS, true)) {
            return false;
        }

        if ($audioCodec === null || $audioCodec === '') {
            return true;
        }

        return in_array(strtolower($audioCodec), self::WEB_COMPATIBLE_AUDIO_CODECS, true);
    }

    private function createFfprobe(): FFProbe
    {
        return FFProbe::create([
            'ffmpeg.binaries' => config('media-library.ffmpeg_path'),
            'ffprobe.binaries' => config('media-library.ffprobe_path'),
            'timeout' => 3600,
        ]);
    }

    private function createFfmpeg(): FFMpeg
    {
        return FFMpeg::create([
            'ffmpeg.binaries' => config('media-library.ffmpeg_path'),
            'ffprobe.binaries' => config('media-library.ffprobe_path'),
            'timeout' => 3600,
        ]);
    }

    private function convertFile(string $inputPath, string $outputPath): void
    {
        $format = new X264('aac');
        // Move moov atom to the start so playback can begin before full download (HTML5 video).
        $format->setAdditionalParameters(['-movflags', '+faststart']);

        $this->createFfmpeg()->open($inputPath)->save($format, $outputPath);
    }

    /**
     * Re-wrap an already compatible file with faststart without re-encoding.
     */
    private function remuxWithFaststart(string $inputPath, string $outputPath): void
    {
        $result = Process::timeout(3600)->run([
            config('media-library.ffmpeg_path', 'ffmpeg'),
            '-y',
            '-i',
            $inputPath,
            '-c',
            'copy',
            '-movflags',
            '+faststart',
            $outputPath,
        ]);

        if (!$result->successful()) {
            throw new RuntimeException(sprintf(
                'FFmpeg remux failed: %s',
                trim($result->errorOutput()) ?: trim($result->output()),
            ));
        }
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
