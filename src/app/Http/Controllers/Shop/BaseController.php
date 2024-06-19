<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

abstract class BaseController extends Controller
{
    /**
     * BaseController constructor.
     */
    public function __construct(protected Request $request) {}
}
