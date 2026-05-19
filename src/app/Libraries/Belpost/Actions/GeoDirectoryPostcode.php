<?php

namespace App\Libraries\Belpost\Actions;

class GeoDirectoryPostcode extends Action
{
    protected string $url = '/api/v1/business/geo-directory/postcode';

    protected string $method = 'get';
}
