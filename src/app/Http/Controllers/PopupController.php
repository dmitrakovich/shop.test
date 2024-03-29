<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class PopupController extends Controller
{
    /**
     * Popup offer to register
     */
    public function offerToRegister(): View
    {
        return view('popups.offers.register');
    }
}
