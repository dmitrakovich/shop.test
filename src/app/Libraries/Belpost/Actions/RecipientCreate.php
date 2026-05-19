<?php

namespace App\Libraries\Belpost\Actions;

class RecipientCreate extends Action
{
    protected string $url = '/api/v1/business/batch-mailing/recipient';

    protected string $method = 'post';
}
