<?php

namespace App\Http\Controllers\Shop;

use Illuminate\Http\Request;

class CatalogController extends BaseController
{
    public function index()
    {
        return view('shop.catalog');
    }
}
