<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::orderBy('id', 'desc')->paginate(20);
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'price' => 'required',
            'image_file' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp|max:10240',
            'lifestyle_file' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp|max:10240',
            'gallery_files.*' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp|max:10240',
        ]);

        $data = $request->except(['_token', 'image_file', 'lifestyle_file', 'gallery_files']);
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        // Upload main image
        if ($request->hasFile('image_file')) {
            $data['image'] = $this->uploadToR2($request->file('image_file'));
        }

        // Upload lifestyle image
        if ($request->hasFile('lifestyle_file')) {
            $data['lifestyle'] = $this->uploadToR2($request->file('lifestyle_file'));
        }

        // Upload gallery images
        if ($request->hasFile('gallery_files')) {
            $urls = [];
            foreach ($request->file('gallery_files') as $file) {
                $urls[] = $this->uploadToR2($file);
            }
            // Merge with any manually entered gallery URLs
            $existing = [];
            if (!empty($data['gallery'])) {
                $decoded = json_decode($data['gallery'], true);
                $existing = is_array($decoded) ? $decoded : [];
            }
            $data['gallery'] = json_encode(array_merge($existing, $urls));
        }

        Product::create($data);

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
    }

    public function show(string $id)
    {
        return redirect()->route('admin.products.edit', $id);
    }

    public function edit(string $id)
    {
        $product = Product::findOrFail($id);
        $categories = Category::orderBy('name')->get();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'price' => 'required',
            'image_file' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp|max:10240',
            'lifestyle_file' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp|max:10240',
            'gallery_files.*' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp|max:10240',
        ]);

        $product = Product::findOrFail($id);
        $data = $request->except(['_token', '_method', 'image_file', 'lifestyle_file', 'gallery_files']);
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        // Upload main image
        if ($request->hasFile('image_file')) {
            $data['image'] = $this->uploadToR2($request->file('image_file'));
        }

        // Upload lifestyle image
        if ($request->hasFile('lifestyle_file')) {
            $data['lifestyle'] = $this->uploadToR2($request->file('lifestyle_file'));
        }

        // Upload gallery images
        if ($request->hasFile('gallery_files')) {
            $urls = [];
            foreach ($request->file('gallery_files') as $file) {
                $urls[] = $this->uploadToR2($file);
            }
            $existing = [];
            if (!empty($data['gallery'])) {
                $decoded = json_decode($data['gallery'], true);
                $existing = is_array($decoded) ? $decoded : [];
            }
            $data['gallery'] = json_encode(array_merge($existing, $urls));
        }

        $product->update($data);

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(string $id)
    {
        Product::findOrFail($id)->delete();
        return redirect()->route('admin.products.index')->with('success', 'Product deleted.');
    }

    /**
     * Upload a file to R2 (S3-compatible) and return the public URL.
     */
    private function uploadToR2($file): string
    {
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $file->storeAs('products', $filename, 's3');
        return rtrim(env('AWS_URL', ''), '/') . '/products/' . $filename;
    }
}
