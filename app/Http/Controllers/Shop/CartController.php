<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class CartController extends Controller
{
    /** The cart itself lives in the browser (localStorage); the page is the shell around it. */
    public function index(): View
    {
        return view('shop.cart.index');
    }
}
