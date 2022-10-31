<?php

namespace App\Libraries\HGrosh\Actions;

use App\Libraries\HGrosh\HttpClient;
use App\Libraries\HGrosh\ApiResponse;

class InvoicingInvoiceQRcode extends Action
{
    protected string $method = 'get';

    /**
     * @param array $params
     * @return ApiResponse
     */
    public function request(array $params = []): ApiResponse
    {
        $params = array_merge($params, $this->params);
        return $this->http_client->{$this->method}('/invoicing/invoice/' . $params['id'] . '/qrcode', $params, $this->getParams);
    }
}
