<?php

namespace App\Libraries\HGrosh;

use App\Libraries\HGrosh\Exceptions\RequestException;
use App\Libraries\HGrosh\Exceptions\TokenException;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Support\Facades\Cache;

class HttpClient
{
    protected Client $client;

    private array $config;

    /**
     * Api constructor.
     *
     * @param  ClientInterface  $http
     */
    public function __construct()
    {
        $this->client = new Client();
        $this->config = config('hgrosh');
    }

    /**
     * Get HGrosh token
     *
     * @return string
     *
     * @throws TokenException
     */
    private function getToken(): string
    {
        $key = $this->config['token_cache_key'] ?? 'hgrosh_api_token';
        if (Cache::has($key)) {
            return Cache::get($key);
        } else {
            $headers = [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ];
            $body = http_build_query([
                'grant_type' => 'client_credentials',
                'client_id' => $this->config['client_id'],
                'scope' => 'epos.public.invoice',
                'client_secret' => $this->config['client_secret'],
                'serviceproviderid' => $this->config['serviceproviderid'],
                'serviceid' => $this->config['serviceid'],
                'retailoutletcode' => $this->config['retailoutletcode'],
            ]);
            $uri = new Uri($this->config['token_url']);
            $request = new Request('POST', $uri, $headers, $body);
            $response = new ApiResponse($this->client->sendRequest($request));
            if ($response->isOk()) {
                $result = $response->getBodyFormat();
                Cache::put($key, $result['access_token'], ((isset($result['expires_in']) && $result['expires_in'] > 700) ? $result['expires_in'] : 3600) - 600);

                return $result['access_token'];
            } else {
                throw new TokenException('Error getting token.');
            }
        }
    }

    /**
     * @param  string  $url
     * @param  array  $params
     * @return ApiResponse
     *
     * @throws RequestException
     */
    public function post(string $url, array $params = [], $getParams = []): ApiResponse
    {
        return $this->request('POST', $url, $params, $getParams);
    }

    /**
     * @param  string  $url
     * @param  array  $params
     * @return ApiResponse
     *
     * @throws RequestException
     */
    public function put(string $url, array $params = []): ApiResponse
    {
        return $this->request('PUT', $url, $params);
    }

    /**
     * @param  string  $url
     * @param  array  $params
     * @return ApiResponse
     *
     * @throws RequestException
     */
    public function delete(string $url, array $params = []): ApiResponse
    {
        return $this->request('DELETE', $url, $params);
    }

    /**
     * @param  string  $url
     * @return ApiResponse
     *
     * @throws RequestException
     */
    public function get(string $url, array $params = []): ApiResponse
    {
        return $this->request('GET', $url, $params);
    }

    /**
     * @param  string  $method
     * @param  string  $url
     * @param  array  $params
     * @return ApiResponse
     *
     * @throws RequestException
     */
    protected function request(string $method, string $url, array $params = [], $getParams = []): ApiResponse
    {
        $url = $this->config['api_url'] . ($url ? ('/' . trim($url, '/')) : '');
        try {
            $headers = [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->getToken(),
            ];
            if ($method === 'GET') {
                $params = array_merge($params, $getParams);
                $url .= empty($params) ? '' : ('?' . http_build_query($params));
                $body = '';
            } else {
                $url .= empty($getParams) ? '' : ('?' . http_build_query($getParams));
                $body = (string) json_encode($params);
            }
            $uri = new Uri($url);
            $request = new Request($method, $uri, $headers, $body);
            $response = $this->client->sendRequest($request);

            return new ApiResponse($response);
        } catch (\Throwable $e) {
            throw new RequestException($e->getMessage(), (int) $e->getCode());
        }
    }
}
