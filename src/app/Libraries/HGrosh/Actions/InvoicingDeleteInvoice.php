<?php

namespace App\Libraries\HGrosh\Actions;

class InvoicingDeleteInvoice extends Action
{
    protected string $url = '/invoicing/invoice'; // URL для запросов к API @var string

    protected string $method = 'delete';
}
