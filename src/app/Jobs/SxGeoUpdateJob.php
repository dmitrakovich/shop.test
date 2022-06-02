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

    /**
     * Путь к скачиваемому файлу
     */
    final const URL = 'https://sypexgeo.net/ru/pc/download/%s/SxGeoCountry.zip';

    protected $jobName = 'Обновление базы Sypex Geo';

    /**
     * Директория для сохранения файлов SxGeo
     */
    protected $sxGeoPath = null;

    /**
     * Файл в котором хранится дата последнего обновления
     */
    protected $lastUpdFile = null;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 600;

    /**
     * Переменные, которые нужно отразить в context
     *
     * @var array
     */
    protected $contextVars = ['lastModified'];

    /**
     * Last db modified date
     */
    protected ?string $lastModified = null;

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

        if (file_exists($this->lastUpdFile)) {
            $this->lastModified = file_get_contents($this->lastUpdFile);
        }

        $response = Http::withHeaders(
            $this->lastModified ? ['If-Modified-Since' => $this->lastModified] : []
        )->withOptions([
            'sink' => fopen($zipFile, 'wb'),
        ])->get($this->getDownloadUrl());

        if ($response->status() == 304) {
            @unlink($zipFile);
            return $this->debug('Архив не обновился, с момента предыдущего скачивания');
        }

        $this->lastModified = $response->header('Last-Modified');
        $this->debug('Архив скачан с сервера. Распаковываем');

        $zip = new ZipArchive;
        if ($zip->open($zipFile) === TRUE) {
            $zip->extractTo($this->sxGeoPath);
            $zip->close();
            unlink($zipFile);
        } else {
            return $this->error('Ошибка при распаковке архива');
        }

        file_put_contents($this->lastUpdFile, $this->lastModified);

        $this->debug('База успешно обновлена');
    }

    /**
     * Generates a download link
     */
    protected function getDownloadUrl(): string
    {
        return sprintf(self::URL, env('SXGEO_TOKEN'));
    }
}
