<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::orderBy('id', 'desc')->paginate(20);
        return view('admin.customers.index', compact('customers'));
    }

    public function show(string $id)
    {
        $customer = Customer::findOrFail($id);
        return view('admin.customers.show', compact('customer'));
    }

    public function destroy(string $id)
    {
        Customer::findOrFail($id)->delete();
        return redirect()->route('admin.customers.index')->with('success', 'Customer deleted.');
    }

    public function create() { abort(404); }
    public function store(Request $request) { abort(404); }
    public function edit(string $id) { abort(404); }
    public function update(Request $request, string $id) { abort(404); }
}
