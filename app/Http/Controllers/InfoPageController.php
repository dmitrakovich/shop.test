<?php

namespace App\Http\Controllers;

use App\Models\InfoPage;
use Illuminate\Http\Request;

class InfoPageController extends Controller
{
    public function index(?string $slug = null)
    {
        $currentInfoPage = InfoPage::when($slug, function ($query) use ($slug) {
            return $query->where('slug', $slug);
        })
        ->firstOrFail(['slug', 'name', 'html'])
        ->toArray();

        return view('static.template', compact('currentInfoPage'));
    }
}
