<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->input('q', ''));

        $query = Product::orderBy('id', 'desc');
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('sku', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$q}%");
                if (is_numeric($q)) {
                    $sub->orWhere('id', (int) $q);
                }
            });
        }

        $products = $query->paginate(20)->appends($request->query());

        return view('admin.products.index', compact('products', 'q'));
    }

    public function create(Request $request)
    {
        $categories = Category::whereNull('parent_id')->with('children')->orderBy('name')->get();
        $returnTo = $this->returnTo($request);

        return view('admin.products.create', compact('categories', 'returnTo'));
    }

    public function store(Request $request)
    {
        $this->validateProduct($request);

        $data = $this->productData($request);

        $product = Product::create($data);
        Cache::flush();

        return redirect($this->returnTo($request))->with('success', 'Product created successfully. Code: ' . $product->sku);
    }

    public function show(string $id)
    {
        return redirect()->route('admin.products.edit', $id);
    }

    public function edit(Request $request, string $id)
    {
        $product = Product::findOrFail($id);
        $categories = Category::whereNull('parent_id')->with('children')->orderBy('name')->get();
        $returnTo = $this->returnTo($request);

        return view('admin.products.edit', compact('product', 'categories', 'returnTo'));
    }

    public function update(Request $request, string $id)
    {
        $product = Product::findOrFail($id);
        $this->validateProduct($request, $product);

        $data = $this->productData($request, $product);

        $product->update($data);
        Cache::flush();

        return redirect($this->returnTo($request))->with('success', 'Product updated successfully.');
    }

    public function destroy(Request $request, string $id)
    {
        Product::findOrFail($id)->delete();
        Cache::flush();

        return redirect($this->returnTo($request))->with('success', 'Product deleted.');
    }

    /**
     * Where to send the admin after saving: the product list page they came
     * from (page number and search kept), never page 1 by default. Only an
     * admin products-list URL is accepted so this can't redirect elsewhere.
     */
    private function returnTo(Request $request): string
    {
        $candidate = (string) ($request->input('return_to') ?: $request->query('return_to') ?: '');
        $base = route('admin.products.index');

        if ($candidate !== '' && (Str::startsWith($candidate, $base) || Str::startsWith($candidate, parse_url($base, PHP_URL_PATH)))) {
            return $candidate;
        }

        // Fall back to the referer when it is the product list (first visit
        // to the edit form from the list has no return_to yet).
        $referer = (string) $request->headers->get('referer', '');
        if ($referer !== '' && Str::startsWith($referer, $base) && !Str::contains($referer, ['/create', '/edit'])) {
            return $referer;
        }

        return $base;
    }

    private function validateProduct(Request $request, ?Product $product = null): void
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'price' => 'required',
            'sku'   => [
                'nullable', 'string', 'max:64',
                Rule::unique('products', 'sku')->ignore($product?->id),
            ],
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'colors' => 'nullable|string|max:20000',
            'image_file' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,avif|max:10240',
            'gallery_files.*' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,avif|max:10240',
        ], [
            'sku.unique' => 'That product code is already used by another product.',
        ]);
    }

    /**
     * Everything both store() and update() do to turn the form into a row.
     */
    private function productData(Request $request, ?Product $product = null): array
    {
        $data = $request->except(['_token', '_method', 'image_file', 'gallery_files', 'return_to']);
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        // Product code: admin-entered, or kept from the existing product, or
        // generated from the id once the row exists (model `created` hook).
        $sku = Product::normaliseSku($request->input('sku'));
        if ($sku === null && $product) {
            $sku = $product->sku ?: Product::defaultSku((int) $product->id);
        }
        $data['sku'] = $sku;

        $data['meta_title'] = $request->filled('meta_title') ? trim((string) $request->input('meta_title')) : null;
        $data['meta_description'] = $request->filled('meta_description') ? trim((string) $request->input('meta_description')) : null;

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
        } elseif (array_key_exists('sizes', $data) && $data['sizes'] === '') {
            $data['sizes'] = [];
        }

        // Colour options: the editor posts a JSON list; stored as a real array.
        $data['colors'] = Product::parseColors($request->input('colors'));

        return $data;
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
        $disk = config('filesystems.default');
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $file->storeAs('products', $filename, $disk);
        return Storage::disk($disk)->url('products/' . $filename);
    }
}
