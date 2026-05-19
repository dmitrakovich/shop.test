<?php

namespace App\Libraries\Belpost;

use App\Libraries\Belpost\Exceptions\BelpostApiException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Low-level HTTP transport for Belpost (token auth, JSON).
 *
 * Action classes and {@see Api} build on this; application services may inject it directly
 * for flows that do not map to a single action (e.g. binary document download).
 */
class HttpClient
{
    private readonly string $baseUrl;

    private readonly ?string $token;

    public function __construct(?string $baseUrl = null, ?string $token = null)
    {
        $this->baseUrl = $baseUrl ?? (string)config('belpost.base_url');
        $this->token = $token ?? config('belpost.token');
    }

    /**
     * @param  array<string, mixed>|null  $query
     * @return array<string, mixed>
     */
    public function get(string $path, ?array $query = null): array
    {
        return $this->request('get', $path, query: $query)->getBodyFormat();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function post(string $path, array $data = []): array
    {
        return $this->request('post', $path, data: $data)->getBodyFormat();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function put(string $path, array $data = []): array
    {
        return $this->request('put', $path, data: $data)->getBodyFormat();
    }

    /**
     * @return array<string, mixed>
     */
    public function delete(string $path): array
    {
        return $this->request('delete', $path)->getBodyFormat();
    }

    /**
     * @param  array<string, mixed>|null  $data
     * @param  array<string, mixed>|null  $query
     */
    public function requestResponse(string $method, string $path, ?array $data = null, ?array $query = null): ApiResponse
    {
        return $this->request($method, $path, $data, $query);
    }

    public function download(string $path): Response
    {
        $response = $this->http()->get($this->url($path));

        if (!$response->successful()) {
            throw BelpostApiException::fromResponse($response, 'Download failed');
        }

        return $response;
    }

    public function downloadDocument(int $documentId): Response
    {
        foreach ([
            "/api/v1/batch-mailing/documents/{$documentId}/download",
            "/api/v1/business/batch-mailing/documents/{$documentId}/download",
        ] as $path) {
            $response = $this->http()->get($this->url($path));

            if ($response->successful()) {
                return $response;
            }

            if ($response->status() === 404) {
                continue;
            }

            throw BelpostApiException::fromResponse($response, $path);
        }

        throw new BelpostApiException(
            "Belpost document #{$documentId} was not found for download.",
            404,
        );
    }

    public function isConfigured(): bool
    {
        return filled($this->token);
    }

    /**
     * @param  array<string, mixed>|null  $data
     * @param  array<string, mixed>|null  $query
     */
    private function request(string $method, string $path, ?array $data = null, ?array $query = null): ApiResponse
    {
        $pending = $this->http();
        $url = $this->url($path);

        $response = match ($method) {
            'get' => $pending->get($url, $query ?? []),
            'post' => $pending->post($url, $data ?? []),
            'put' => $pending->put($url, $data ?? []),
            'delete' => $pending->delete($url),
            default => throw new BelpostApiException("Unsupported HTTP method: {$method}"),
        };

        if (!$response->successful()) {
            throw BelpostApiException::fromResponse($response, $path);
        }

        $json = $response->json();

        return new ApiResponse(
            $response->status(),
            is_array($json) ? $json : [],
        );
    }

    private function http(): PendingRequest
    {
        if (!$this->isConfigured()) {
            throw new BelpostApiException('Belpost API token is not configured (BELPOST_API_TOKEN).');
        }

        return Http::baseUrl(rtrim($this->baseUrl, '/'))
            ->withToken($this->token)
            ->acceptJson()
            ->asJson()
            ->timeout(60);
    }

    private function url(string $path): string
    {
        return str_starts_with($path, '/') ? $path : '/' . $path;
    }
}
