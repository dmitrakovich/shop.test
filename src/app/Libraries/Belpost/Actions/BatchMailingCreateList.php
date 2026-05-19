<?php

namespace App\Libraries\Belpost\Actions;

class BatchMailingCreateList extends Action
{
    protected string $url = '/api/v1/business/batch-mailing/list';

    protected string $method = 'post';
}
