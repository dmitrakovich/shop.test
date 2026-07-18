<?php

namespace Tests\Unit\Services\Media;

use App\Jobs\Media\PerformConversionsJob;
use App\Listeners\Media\ConvertVideo;
use App\Services\Media\VideoConversionService;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Spatie\MediaLibrary\Conversions\FileManipulator;
use Spatie\MediaLibrary\MediaCollections\Filesystem;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Tests\TestCase;

class VideoConversionServiceTest extends TestCase
{
    public function test_are_codecs_web_compatible(): void
    {
        $service = app(VideoConversionService::class);

        $this->assertTrue($service->areCodecsWebCompatible('h264', 'aac'));
        $this->assertTrue($service->areCodecsWebCompatible('H264', null));
        $this->assertFalse($service->areCodecsWebCompatible('hevc', 'aac'));
        $this->assertFalse($service->areCodecsWebCompatible('h264', 'mp3'));
    }

    public function test_media_queue_timeout_chain_is_ordered(): void
    {
        $jobTimeout = ConvertVideo::TIMEOUT_SECONDS;
        $horizonTimeout = (int)config('horizon.environments.production.supervisor-media.timeout');
        $mediaConnection = (string)config('horizon.environments.production.supervisor-media.connection');
        $longRetryAfter = (int)config('queue.connections.redis-long.retry_after');
        $defaultRetryAfter = (int)config('queue.connections.redis.retry_after');

        $this->assertSame(600, $jobTimeout);
        $this->assertSame($jobTimeout, (new ConvertVideo(app(VideoConversionService::class)))->timeout);

        $performConversionsJob = (new \ReflectionClass(PerformConversionsJob::class))->newInstanceWithoutConstructor();
        $this->assertSame($jobTimeout, $performConversionsJob->timeout);

        $this->assertSame('redis-long', $mediaConnection);
        $this->assertSame(90, $defaultRetryAfter);
        $this->assertGreaterThan($jobTimeout, $horizonTimeout);
        $this->assertGreaterThan($horizonTimeout, $longRetryAfter);
    }

    public function test_recovers_when_full_mp4_exists_after_source_was_deleted(): void
    {
        Storage::fake('local');

        $media = new class extends Media
        {
            public function getPathRelativeToRoot(string $conversionName = ''): string
            {
                if ($conversionName !== '') {
                    return 'media/1-' . $conversionName . '.jpg';
                }

                return 'media/1-clip.mov';
            }

            public function updateQuietly(array $attributes = [], array $options = []): bool
            {
                foreach ($attributes as $key => $value) {
                    $this->setAttribute($key, $value);
                }

                return true;
            }

            public function refresh(): static
            {
                return $this;
            }

            /**
             * @param  array<int, string>|string  $with
             */
            public function fresh($with = []): static
            {
                return $this;
            }
        };

        $media->forceFill([
            'id' => 1,
            'disk' => 'local',
            'conversions_disk' => 'local',
            'file_name' => 'clip.mov',
            'mime_type' => 'video/quicktime',
            'size' => 100,
            'collection_name' => 'videos',
            'generated_conversions' => [],
        ]);
        $media->syncOriginal();

        Storage::disk('local')->put('media/1-full.mp4', 'converted-mp4-bytes');

        $filesystem = Mockery::mock(Filesystem::class);
        $filesystem->shouldReceive('getMediaDirectory')->andReturn('media/1-');
        $filesystem->shouldReceive('removeFile')->never();

        $fileManipulator = Mockery::mock(FileManipulator::class);
        $fileManipulator->shouldReceive('createDerivedFiles')->once()->andReturnNull();

        $service = new VideoConversionService($filesystem, $fileManipulator);

        $this->assertTrue($service->convertToMp4($media));
        $this->assertSame('full.mp4', $media->file_name);
        $this->assertSame('video/mp4', $media->mime_type);
        $this->assertSame(strlen('converted-mp4-bytes'), $media->size);
    }

    public function test_throws_when_source_and_full_mp4_are_both_missing(): void
    {
        Storage::fake('local');

        $media = new class extends Media
        {
            public function getPathRelativeToRoot(string $conversionName = ''): string
            {
                return 'media/1-clip.mov';
            }
        };

        $media->forceFill([
            'id' => 1,
            'disk' => 'local',
            'file_name' => 'clip.mov',
            'mime_type' => 'video/quicktime',
            'size' => 100,
            'collection_name' => 'videos',
            'generated_conversions' => [],
        ]);

        $filesystem = Mockery::mock(Filesystem::class);
        $filesystem->shouldReceive('getMediaDirectory')->andReturn('media/1-');

        $service = new VideoConversionService(
            $filesystem,
            Mockery::mock(FileManipulator::class),
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Source video not found on disk for media #1');

        $service->convertToMp4($media);
    }
}
