<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbandonedCart;

class AbandonedCartController extends Controller
{
    public function index()
    {
        $carts = AbandonedCart::orderBy('id', 'desc')->paginate(25);
        return view('admin.abandoned-carts.index', compact('carts'));
    }

    public function destroy(string $id)
    {
        AbandonedCart::findOrFail($id)->delete();
        return redirect()->route('admin.abandoned-carts.index')->with('success', 'Cart record deleted.');
    }

    public function create() { abort(404); }
    public function store(\Illuminate\Http\Request $r) { abort(404); }
    public function show(string $id) { abort(404); }
    public function edit(string $id) { abort(404); }
    public function update(\Illuminate\Http\Request $r, string $id) { abort(404); }
}
