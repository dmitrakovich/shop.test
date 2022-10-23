<?php

namespace App\Libraries\HGrosh\Actions;

use App\Libraries\HGrosh\HttpClient;
use App\Libraries\HGrosh\ApiResponse;

class Action
{
    public const URL = ''; // URL для запросов к API @var string
    protected HttpClient $http_client; // Объект для взаимодействия с API
    protected string $method = 'post';
    protected array $params = [];

    /**
     * Action constructor.
     * @param Api $request
     */
    public function __construct(HttpClient $request)
    {
        $this->http_client = $request;
    }

    /**
     * @param array $params
     * @return ApiResponse
     */
    public function request(array $params = []): ApiResponse
    {
        $params = array_merge($params, $this->params);
        return $this->http_client->{$this->method}(static::URL, $params);
    }
}
