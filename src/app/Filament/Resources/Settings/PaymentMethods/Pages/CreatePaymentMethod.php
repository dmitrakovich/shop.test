<?php

namespace App\Filament\Resources\Settings\PaymentMethods\Pages;

use App\Filament\Resources\Settings\PaymentMethods\PaymentMethodResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePaymentMethod extends CreateRecord
{
    protected static string $resource = PaymentMethodResource::class;
}
