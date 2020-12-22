<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DebugController extends Controller
{
    public function index()
    {
        list($a, $b, $c) = [1, 2];
        dd($a, $b, $c);
        dd(Auth::id());
        dd(Auth::user());
    }
}
