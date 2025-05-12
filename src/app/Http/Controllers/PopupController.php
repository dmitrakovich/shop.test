<?php

namespace App\Http\Controllers;

use App\Facades\Device;

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

    /**
     * Popup new site
     */
    public function newSite(): View
    {
        return view('popups.new-site', [
            'deviceId' => Device::current()?->web_id,
        ]);
    }
}
