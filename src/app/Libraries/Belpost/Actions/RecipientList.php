<?php

namespace App\Libraries\Belpost\Actions;

class RecipientList extends Action
{
    protected string $url = '/api/v1/business/batch-mailing/recipient';

    protected string $method = 'get';
}
