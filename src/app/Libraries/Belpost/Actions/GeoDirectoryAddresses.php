<?php

namespace App\Libraries\Belpost\Actions;

class GeoDirectoryAddresses extends Action
{
    protected string $url = '/api/v1/business/geo-directory/addresses';

    protected string $method = 'get';
}
