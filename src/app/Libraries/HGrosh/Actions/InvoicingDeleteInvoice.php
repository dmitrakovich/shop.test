<?php

namespace App\Libraries\HGrosh\Actions;

class InvoicingDeleteInvoice extends Action
{
    public const URL  = '/invoicing/invoice'; //URL для запросов к API @var string
    protected string $method = 'delete';
}
