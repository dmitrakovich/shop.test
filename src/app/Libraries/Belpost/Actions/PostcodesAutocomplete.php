<?php

namespace App\Libraries\Belpost\Actions;

class PostcodesAutocomplete extends Action
{
    protected string $url = '/api/v1/postcodes/autocomplete';

    protected string $method = 'get';
}
