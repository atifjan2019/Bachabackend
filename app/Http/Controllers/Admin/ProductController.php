<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::orderBy('id', 'desc')->paginate(20);
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::whereNull('parent_id')->with('children')->orderBy('name')->get();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'price' => 'required',
            'image_file' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp|max:10240',
            'gallery_files.*' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp|max:10240',
        ]);

        $data = $request->except(['_token', 'image_file', 'gallery_files']);
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        // Merchandising flags — checkboxes only submit when checked, so set explicitly.
        $data['is_new'] = $request->boolean('is_new');
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_best_seller'] = $request->boolean('is_best_seller');
        $data['label'] = $request->filled('label') ? $request->input('label') : null;

        // Upload main image
        if ($request->hasFile('image_file')) {
            $data['image'] = $this->uploadToR2($request->file('image_file'));
        }



        // Gallery: combine library-selected URLs (hidden field) with any uploaded files.
        // Stored as a real array so the model's `array` cast JSON-encodes it exactly once.
        $data['gallery'] = $this->buildGallery($request, $data['gallery'] ?? null);

        // Parse sizes from comma separated string
        if (!empty($data['sizes']) && is_string($data['sizes'])) {
            $cleaned = trim($data['sizes']);
            if (Str::startsWith($cleaned, '[') && Str::endsWith($cleaned, ']')) {
                // assume json
                $data['sizes'] = json_decode($cleaned, true) ?? [];
            } else {
                // comma separated
                $data['sizes'] = array_values(array_filter(array_map('trim', explode(',', $cleaned))));
            }
        }

        Product::create($data);
        Cache::flush();

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
    }

    public function show(string $id)
    {
        return redirect()->route('admin.products.edit', $id);
    }

    public function edit(string $id)
    {
        $product = Product::findOrFail($id);
        $categories = Category::whereNull('parent_id')->with('children')->orderBy('name')->get();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'price' => 'required',
            'image_file' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp|max:10240',
            'gallery_files.*' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp|max:10240',
        ]);

        $product = Product::findOrFail($id);
        $data = $request->except(['_token', '_method', 'image_file', 'gallery_files']);
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        // Merchandising flags — checkboxes only submit when checked, so set explicitly.
        $data['is_new'] = $request->boolean('is_new');
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_best_seller'] = $request->boolean('is_best_seller');
        $data['label'] = $request->filled('label') ? $request->input('label') : null;

        // Upload main image
        if ($request->hasFile('image_file')) {
            $data['image'] = $this->uploadToR2($request->file('image_file'));
        }



        // Gallery: combine library-selected/existing URLs (hidden field) with any uploaded files.
        // Stored as a real array so the model's `array` cast JSON-encodes it exactly once.
        $data['gallery'] = $this->buildGallery($request, $data['gallery'] ?? null);

        // Parse sizes from comma separated string
        if (!empty($data['sizes']) && is_string($data['sizes'])) {
            $cleaned = trim($data['sizes']);
            if (Str::startsWith($cleaned, '[') && Str::endsWith($cleaned, ']')) {
                // assume json
                $data['sizes'] = json_decode($cleaned, true) ?? [];
            } else {
                // comma separated
                $data['sizes'] = array_values(array_filter(array_map('trim', explode(',', $cleaned))));
            }
        }

        $product->update($data);
        Cache::flush();

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(string $id)
    {
        Product::findOrFail($id)->delete();
        Cache::flush();
        return redirect()->route('admin.products.index')->with('success', 'Product deleted.');
    }

    /**
     * Build the gallery array: existing/library-selected URLs (from the hidden `gallery`
     * field, sent as a JSON array) plus any newly uploaded `gallery_files`.
     * Returns a plain array — the model's `array` cast handles JSON encoding.
     */
    private function buildGallery(Request $request, $hidden): array
    {
        $gallery = [];
        if (!empty($hidden)) {
            $decoded = is_array($hidden) ? $hidden : json_decode($hidden, true);
            $gallery = is_array($decoded) ? $decoded : [];
        }

        if ($request->hasFile('gallery_files')) {
            foreach ($request->file('gallery_files') as $file) {
                $gallery[] = $this->uploadToR2($file);
            }
        }

        return array_values(array_filter($gallery, fn ($url) => is_string($url) && $url !== ''));
    }

    /**
     * Upload a file to R2 (S3-compatible) and return the public URL.
     */
    private function uploadToR2($file): string
    {
        $disk = env('FILESYSTEM_DISK', 'public');
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $file->storeAs('products', $filename, $disk);
        return Storage::disk($disk)->url('products/' . $filename);
    }
}
