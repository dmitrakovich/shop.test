<?php

namespace App\Services;

use App\Models\Api\Token;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

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
        'permalink',
    ];

    /**
     * Base url for request
     */
    protected const BASE_URL = 'https://graph.instagram.com';

    /**
     * Token lifetime
     */
    final const TTL_DAYS = 50;

    /**
     * Cache keys
     */
    final const CACHE_POSTS_KEY = 'instagram_posts';

    final const CACHE_TITLE_KEY = 'instagram_title';

    /**
     * Media types
     */
    final const MEDIA_TYPE_IMAGE = ['IMAGE', 'CAROUSEL_ALBUM'];

    final const MEDIA_TYPE_VIDEO = ['VIDEO', 'REELS'];

    /**
     * Get access token
     */
    protected function getAccessToken(): string
    {
        /** @var Token|null $token */
        $token = Token::instagram()->first();

        if (empty($token)) {
            throw new \Exception('Instagram token not exists');
        }
        $this->updateIfNeeded($token);

        return $token;
    }

    /**
     * Update access token if needed
     */
    protected function updateIfNeeded(Token $token): void
    {
        if ($token->isExpired()) {
            $token->updateToken($this->getNewToken($token), now()->addDays(self::TTL_DAYS));
        }
    }

    /**
     * Update access token
     */
    protected function getNewToken(string $oldToken): string
    {
        $response = Http::get(self::BASE_URL . '/refresh_access_token', [
            'grant_type' => 'ig_refresh_token',
            'access_token' => $oldToken,
        ]);

        if ($this->captureException($response)) {
            return $oldToken;
        }

        return $response->json('access_token');
    }

    /**
     * Get last 25 instagram posts
     */
    public function getPosts(): array
    {
        $response = Http::get(self::BASE_URL . '/me/media', [
            'fields' => implode(',', self::POSTS_FIELDS),
            'access_token' => $this->getAccessToken(),
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
        return Cache::remember(self::CACHE_POSTS_KEY, 3600, fn () => $this->getPosts());
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
            \Sentry\captureException(new \Exception($errorMsg));

            return true;
        }

        return false;
    }

    /**
     * Filter data without required fields & filter video posts
     */
    public function filterWrongData(array $data): array
    {
        return array_filter($data, function (array $post) {
            return isset($post['caption']) && in_array($post['media_type'], self::MEDIA_TYPE_IMAGE);
        });
    }
}
