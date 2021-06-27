<?php

namespace App\Jobs;

use ZipArchive;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Http;

class SxGeoUpdateJob extends AbstractJob
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    const URL = 'https://sypexgeo.net/files/SxGeoCountry.zip';  // Путь к скачиваемому файлу

    protected $jobName = 'Обновление базы Sypex Geo';
    /**
     * Директория для сохранения файлов SxGeo
     */
    protected ?string $sxGeoPath = null;
    /**
     * Файл в котором хранится дата последнего обновления
     */
    protected ?string $lastUpdFile = null;
    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 600;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->sxGeoPath = database_path('sxgeo');
        $this->lastUpdFile = $this->sxGeoPath . '/SxGeo.upd';
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->debug('Старт. Скачиваем архив с сервера');

        $zipFile = $this->sxGeoPath . '/SxGeoTmp.zip';

        $response = Http::withHeaders(
            file_exists($this->lastUpdFile) ? ['If-Modified-Since' => file_get_contents($this->lastUpdFile)] : []
        )->withOptions([
            'sink' => fopen($zipFile, 'wb'),
        ])->get(self::URL);

        if ($response->status() == 304) {
            @unlink($zipFile);
            return $this->debug('Архив не обновился, с момента предыдущего скачивания');
        }

        $this->debug('Архив скачан с сервера. Распаковываем');

        $zip = new ZipArchive;
        if ($zip->open($zipFile) === TRUE) {
            $zip->extractTo($this->sxGeoPath);
            $zip->close();
            unlink($zipFile);
        } else {
            return $this->error('Ошибка при распаковке архива');
        }

        file_put_contents($this->lastUpdFile, gmdate('D, d M Y H:i:s') . ' GMT');

        $this->debug('База успешно обновлена');
    }
}
