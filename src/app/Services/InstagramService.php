<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class InstagramService
{
    /**
     * Base url for request
     *
     * @var string
     */
    protected $baseUrl = 'https://graph.instagram.com';

    /**
     * API access token
     *
     * @var string
     */
    protected $accessToken;

    /**
     * Token lifetime
     *
     * @var integer
     */
    protected $tokenLifetime = 4320000; // 50 days

    public function __construct()
    {
        $this->accessToken = $this->getAccessToken();;
    }

    /**
     * Get access token
     *
     * @return string
     */
    protected function getAccessToken(): string
    {
        $file = database_path('files/instagram_api_token.php');

        if (!file_exists($file)) {
            throw new \Exception('Instagram token not exists');
        }
        $this->updateIfNeeded($file);

        return require $file;
    }

    /**
     * Update access token if needed
     *
     * @param string $file
     * @return void
     */
    protected function updateIfNeeded(string $file): void
    {
        if (time() > (filectime($file) + $this->tokenLifetime)) {
            $token = $this->updateToken(require $file);
            file_put_contents($file, "<?php return '$token';");
        }
    }

    /**
     * Update access token
     *
     * @param string $oldToken
     * @return string
     */
    protected function updateToken(string $oldToken): string
    {
        $response = Http::get($this->baseUrl . '/refresh_access_token', [
            'grant_type' => 'ig_refresh_token',
            'access_token' => $oldToken
        ]);

        return $response->json('access_token');
    }

    /**
     * Get last 25 instagram posts
     *
     * @return array
     */
    public function getPosts(): array
    {
        $response = Http::get($this->baseUrl . '/me/media', [
            'fields' => implode(',', [
                'id',
                'media_type',
                'media_url',
                'caption',
                'timestamp',
                'thumbnail_url',
                'permalink'
            ]),
            'access_token' => $this->accessToken
        ]);

        return $response->json('data');
    }

    /**
     * Get last 25 instagram posts use cache
     *
     * @return array
     */
    public function getCachedPosts(): array
    {
        return Cache::remember('instagram_posts', 3600, function () { // 1h
            return $this->getPosts();
        });
    }
}
