<?php

namespace App\Libraries\Belpost\Actions;

use App\Libraries\Belpost\HttpClient;

class BatchMailingGetList extends Action
{
    protected string $method = 'get';

    public function __construct(HttpClient $httpClient, array $arguments = [])
    {
        parent::__construct($httpClient, $arguments);
        $this->url = isset($arguments[0])
            ? '/api/v1/business/batch-mailing/list/' . (int)$arguments[0]
            : '/api/v1/business/batch-mailing/list';
    }
}
