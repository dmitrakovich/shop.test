<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AppResource;

class AppController extends Controller
{
    public function init(): AppResource
    {
        return new AppResource();
    }
}
