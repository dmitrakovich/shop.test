<?php

namespace App\Libraries\HGrosh\Actions;

use App\Libraries\HGrosh\ApiResponse;
use App\Libraries\HGrosh\HttpClient;

class Action
{
    protected string $url = ''; // URL для запросов к API @var string

    protected HttpClient $http_client; // Объект для взаимодействия с API

    protected string $method = 'post';

    protected array $params = [];

    protected array $getParams = [];

    protected array $arguments = [];

    /**
     * Action constructor.
     *
     * @param  Api  $request
     */
    public function __construct(HttpClient $request, $arguments)
    {
        $this->http_client = $request;
        $this->arguments = $arguments;
    }

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
