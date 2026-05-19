<?php

namespace App\Libraries\Belpost\Actions;

use App\Libraries\Belpost\HttpClient;

class BatchMailingGenerateListItemBlank extends Action
{
    protected string $method = 'post';

    public function __construct(HttpClient $httpClient, array $arguments = [])
    {
        parent::__construct($httpClient, $arguments);
        $listId = (int)($arguments[0] ?? 0);
        $itemId = (int)($arguments[1] ?? 0);
        $this->url = "/api/v1/business/batch-mailing/list/{$listId}/item/{$itemId}/generate-blank";
    }
}
