<?php

namespace App\Services\Api;

use Illuminate\Support\Facades\Http;

/**
 * @todo если будут другие сервисы яндекса, кроме яндекс диска, то вынести в отдельные классы
 */
class YandexApiService
{
    /**
     * Api token
     *
     * @var string
     */
    protected $token;

    public function __construct()
    {
        $this->token = config('services.yandex.token');
    }

    /**
     * Get leftovers file info from yandex disk
     *
     * @return string|null
     */
    public function getLeftoversFileInfo(): ?array
    {
        return $this->get(
            'https://cloud-api.yandex.net:443/v1/disk/resources',
            ['path' => '/Ostatki/ostatki.txt', 'field' => 'modified,md5']
        );
    }

    /**
     * Get leftovers download link from yandex disk
     */
    public function getLeftoversDownloadLink(): ?string
    {
        return $this->get(
            'https://cloud-api.yandex.net:443/v1/disk/resources/download',
            ['path' => '/Ostatki/ostatki.txt']
        )['href'] ?? null;
    }

    /**
     * Send GET query to yandex api
     *
     * @return mixed
     */
    protected function get(string $url, array $params = [])
    {
        return Http::withToken($this->token, 'OAuth')
            ->get($url, $params)
            ->json();
    }
}
