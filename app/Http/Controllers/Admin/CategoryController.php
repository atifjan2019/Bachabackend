<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

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
        $request->validate([
            'name' => 'required|string|max:100|unique:categories',
            'image_file' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp|max:10240',
        ]);
        $data = $request->except(['_token', 'image_file']);
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('categories', $filename, 's3');
            $data['image'] = rtrim(env('AWS_URL', ''), '/') . '/categories/' . $filename;
        }

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
        $request->validate([
            'name' => 'required|string|max:100|unique:categories,name,'.$id,
            'image_file' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp|max:10240',
        ]);
        $data = $request->except(['_token', '_method', 'image_file']);
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('categories', $filename, 's3');
            $data['image'] = rtrim(env('AWS_URL', ''), '/') . '/categories/' . $filename;
        }

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
