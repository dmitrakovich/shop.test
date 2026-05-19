<?php

namespace App\Libraries\Belpost\Actions;

class BatchMailingGetDocuments extends Action
{
    protected string $url = '/api/v1/business/batch-mailing/documents';

    protected string $method = 'get';
}
