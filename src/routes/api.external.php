<?php

use App\Http\Controllers\Shop\PaymentController;
use Illuminate\Support\Facades\Route;

Route::post('/payment/webhook/{code}', [PaymentController::class, 'webhook']);
