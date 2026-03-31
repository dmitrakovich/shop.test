<?php

namespace App\Libraries\HGrosh\Actions;

use App\Libraries\HGrosh\ApiResponse;
use App\Libraries\HGrosh\HttpClient;

class Action
{
    protected string $url = ''; // URL для запросов к API @var string

    protected string $method = 'post';

    /**
     * @var array<array-key, mixed>
     */
    protected array $params = [];

    /**
     * @var array<array-key, mixed>
     */
    protected array $getParams = [];

    /**
     * Action constructor.
     *
     * @param  array<array-key, mixed>  $arguments
     */
    public function __construct(protected HttpClient $http_client, protected array $arguments) {}

    /**
     * Add get param
     */
    public function addGetParam(array $param): self
    {
        $this->getParams = array_merge($param, $this->getParams);

        return $this;
    }

    public function request(array $params = []): ApiResponse
    {
        $params = array_merge($params, $this->params);

        return $this->http_client->{$this->method}($this->url, $params, $this->getParams);
    }
}
