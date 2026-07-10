<?php

namespace App\Services\Api\Yandex;

use Illuminate\Support\Facades\Http;

class DiskService
{
    protected string $token;

    public function __construct()
    {
        $this->token = (string)config('services.yandex.token');
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getLeftoversFileInfo(): ?array
    {
        return $this->get(
            'https://cloud-api.yandex.net:443/v1/disk/resources',
            ['path' => '/Ostatki/ostatki.txt', 'field' => 'modified,md5'],
        );
    }

    public function getLeftoversDownloadLink(): ?string
    {
        $href = ($this->get(
            'https://cloud-api.yandex.net:443/v1/disk/resources/download',
            ['path' => '/Ostatki/ostatki.txt'],
        ) ?? [])['href'] ?? null;

        return is_string($href) ? $href : null;
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>|null
     */
    protected function get(string $url, array $params = []): ?array
    {
        $result = Http::withToken($this->token, 'OAuth')
            ->get($url, $params)
            ->json();

        return is_array($result) ? $result : null;
    }
}
