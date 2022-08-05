<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class InstagramService
{
    /**
     * Fields to get
     */
    const POSTS_FIELDS = [
        'id',
        'media_type',
        'media_url',
        'caption',
        'timestamp',
        'thumbnail_url',
        'permalink'
    ];

    /**
     * Base url for request
     */
    protected string $baseUrl = 'https://graph.instagram.com';

    /**
     * API access token
     */
    protected string $accessToken;

    /**
     * Token lifetime (50 days)
     */
    protected int $tokenLifetime = 4320000;

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
            'fields' => implode(',', self::POSTS_FIELDS),
            'access_token' => $this->accessToken
        ]);

        if ($this->captureException($response)) {
            return [];
        }

        return $this->filterWrongData($response->json()['data']);
    }

    /**
     * Get last 25 instagram posts use cache (1h)
     */
    public function getCachedPosts(): array
    {
        return Cache::remember(self::CACHE_POSTS_KEY, 3600, fn() => $this->getPosts());
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

    /**
     * Capture and log error if exist
     */
    public function captureException(Response $response): bool
    {
        $data = $response->json() ?? $response->body();

        if ($response->failed() || isset($data['error']) || empty($data['data']) || is_string($data)) {
            $errorMsg = $data['error']['message'] ?? (is_string($data) ? $data : null) ?? 'Unknown instagram api error';
            Log::error(new \Exception($errorMsg));
            return true;
        }

        return false;
    }

    /**
     * Filter data without required fields
     */
    public function filterWrongData(array $data): array
    {
        return array_filter($data, fn (array $post) => isset($post['caption']));
    }
}
