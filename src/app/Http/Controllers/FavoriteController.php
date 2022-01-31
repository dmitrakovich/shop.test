<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Favorite;
use App\Services\ProductService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    /**
     * Display a listing of the favorites.
     *
     * @param ProductService $productService
     * @return View
     */
    public function index(ProductService $productService): View
    {
        $favorites = Favorite::limit(100)->pluck('product_id')->toArray();
        $products = $productService->getById($favorites);

        return view('dashboard.favorites', compact('products'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $favorite = Favorite::create([
            'user_id' => Auth::id(),
            'device_id' => Device::getId(),
            'product_id' => (int)$request->input('productId'),
        ]);

        return $favorite->id;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $productId = (int)$request->route('favorite');

        return Favorite::where('product_id', $productId)->delete();
    }
}
