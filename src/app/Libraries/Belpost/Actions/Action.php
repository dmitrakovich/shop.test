<?php

namespace App\Libraries\Belpost\Actions;

use App\Libraries\Belpost\ApiResponse;
use App\Libraries\Belpost\HttpClient;

/**
 * Base class for a single Belpost API endpoint (URL + HTTP method).
 */
class Action
{
    protected string $url = '';

    protected string $method = 'post';

    /**
     * @var array<string, mixed>
     */
    protected array $params = [];

    /**
     * @var array<string, mixed>
     */
    protected array $query = [];

    /**
     * @param  array<int, mixed>  $arguments
     */
    public function __construct(
        protected HttpClient $httpClient,
        protected array $arguments = [],
    ) {}

    /**
     * @param  array<string, mixed>  $query
     */
    public function addQuery(array $query): self
    {
        $this->query = array_merge($query, $this->query);

        return $this;
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function request(array $params = []): ApiResponse
    {
        $params = array_merge($this->params, $params);

        return $this->httpClient->requestResponse(
            $this->method,
            $this->url,
            in_array($this->method, ['get', 'delete'], true) ? null : $params,
            in_array($this->method, ['get', 'delete'], true) ? array_merge($this->query, $params) : $this->query,
        );
    }
}
