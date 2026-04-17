<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class ApiReferenceController extends Controller
{
    public function index()
    {
        return view('admin.api-reference.index');
    }
}
