<?php

namespace App\Libraries\HGrosh\Actions;

class InvoicingEditInvoice extends Action
{
    protected string $url = '/invoicing/invoice'; // URL для запросов к API @var string

    protected string $method = 'put';
}
