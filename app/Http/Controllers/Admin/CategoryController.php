<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('name')->paginate(20);
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100|unique:categories']);
        $data = $request->except('_token');
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        Category::create($data);
        return redirect()->route('admin.categories.index')->with('success', 'Category created.');
    }

    public function edit(string $id)
    {
        $category = Category::findOrFail($id);
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, string $id)
    {
        $category = Category::findOrFail($id);
        $request->validate(['name' => 'required|string|max:100|unique:categories,name,'.$id]);
        $data = $request->except(['_token', '_method']);
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $category->update($data);
        return redirect()->route('admin.categories.index')->with('success', 'Category updated.');
    }

    public function destroy(string $id)
    {
        Category::findOrFail($id)->delete();
        return redirect()->route('admin.categories.index')->with('success', 'Category deleted.');
    }

    public function show(string $id) { return redirect()->route('admin.categories.edit', $id); }
}
