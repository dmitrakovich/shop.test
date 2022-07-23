<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

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

    /**
     * Cache keys
     */
    final const CACHE_POSTS_KEY = 'instagram_posts';
    final const CACHE_TITLE_KEY = 'instagram_title';

    public function __construct()
    {
        $this->accessToken = $this->getAccessToken();;
    }

    /**
     * Get access token
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

        $data = $response->json();

        if ($response->failed() || isset($data['error']) || empty($data['data'])) {
            Log::error(new \Exception($data['error']['message'] ?? 'Unknown instagram api error'));
            return [];
        }

        return array_filter($data['data'], fn (array $post) => isset($post['caption']));
    }

    /**
     * Get last 25 instagram posts use cache
     */
    public function getCachedPosts(): array
    {
        return Cache::remember(self::CACHE_POSTS_KEY, 3600, fn() => $this->getPosts()); // 1h
    }

    /**
     * Get title from admin panel
     */
    public function getTitle(): ?string
    {
        return Cache::get(self::CACHE_TITLE_KEY);
    }

    /**
     * Set new title for instagram
     */
    public function setTitle(?string $title): void
    {
        if (empty($title)) {
            Cache::forget(self::CACHE_TITLE_KEY);
        } else {
            Cache::forever(self::CACHE_TITLE_KEY, $title);
        }
    }
}
