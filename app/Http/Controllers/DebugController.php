<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DebugController extends Controller
{
    public function index()
    {
        dd(Auth::id());
        dd(Auth::user());
    }
}
