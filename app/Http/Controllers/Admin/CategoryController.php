<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    public const SORT_ORDER_RULE = 'nullable|integer|min:0|max:999999';

    public function index()
    {
        // Listed in storefront order so the admin sees the effect of the
        // numbers they type (numbered first, then creation order).
        $categories = Category::withCount('children')->ordered()->paginate(20);

        // withCount('products') only matches products filed under this exact
        // slug, so every parent showed "0 products" while its children held the
        // stock. Count the subtree instead, matching what the storefront API
        // reports and what opening the category actually shows.
        $counts = Category::subtreeProductCounts();
        $categories->each(
            fn (Category $c) => $c->setAttribute('products_count', $counts[$c->id] ?? 0)
        );

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $parentCategories = Category::whereNull('parent_id')->orderBy('name')->get();
        return view('admin.categories.create', compact('parentCategories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:categories',
            'parent_id' => 'nullable|exists:categories,id',
            'image_file' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,avif|max:10240',
            'sort_order' => self::SORT_ORDER_RULE,
            'image_alt' => 'nullable|string|max:255',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
        ]);
        $data = $request->except(['_token', 'image_file']);
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $data = array_merge($data, $this->visibility($request));

        if ($request->hasFile('image_file')) {
            $data['image'] = media_store($request->file('image_file'), 'categories');
        }

        Category::create($data);
        Cache::flush();
        return redirect()->route('admin.categories.index')->with('success', 'Category created.');
    }

    public function edit(string $id)
    {
        $category = Category::findOrFail($id);
        $parentCategories = Category::whereNull('parent_id')->where('id', '!=', $id)->orderBy('name')->get();
        return view('admin.categories.edit', compact('category', 'parentCategories'));
    }

    public function update(Request $request, string $id)
    {
        $category = Category::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:100|unique:categories,name,'.$id,
            'parent_id' => 'nullable|exists:categories,id',
            'image_file' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,avif|max:10240',
            'sort_order' => self::SORT_ORDER_RULE,
            'image_alt' => 'nullable|string|max:255',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
        ]);
        $data = $request->except(['_token', '_method', 'image_file']);
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $data = array_merge($data, $this->visibility($request));

        if ($request->hasFile('image_file')) {
            $data['image'] = media_store($request->file('image_file'), 'categories');
        }

        $category->update($data);
        Cache::flush();
        return redirect()->route('admin.categories.index')->with('success', 'Category updated.');
    }

    public function destroy(string $id)
    {
        Category::findOrFail($id)->delete();
        Cache::flush();
        return redirect()->route('admin.categories.index')->with('success', 'Category deleted.');
    }

    public function show(string $id) { return redirect()->route('admin.categories.edit', $id); }

    /**
     * Inline edit from the list: the status switch and the order box post
     * just the field they changed, so the admin never leaves the page.
     */
    public function quick(Request $request, string $id)
    {
        $category = Category::findOrFail($id);
        $data = $request->validate([
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'sometimes|'.self::SORT_ORDER_RULE,
        ]);
        if (array_key_exists('sort_order', $data)) {
            $data['sort_order'] = $data['sort_order'] === null || $data['sort_order'] === '' ? null : (int) $data['sort_order'];
        }

        $category->update($data);
        Cache::flush();

        return response()->json(['ok' => true, 'is_active' => $category->is_active, 'sort_order' => $category->sort_order]);
    }

    /**
     * Drag-and-drop from the list: the ids of the rows on the page in their
     * new order, plus how many rows precede the page. Every dropped row gets
     * an explicit position (offset + place), so the order holds across pages
     * and the number boxes update to match.
     */
    public function reorder(Request $request)
    {
        $data = $request->validate([
            'ids' => 'required|array|min:1|max:200',
            'ids.*' => 'integer|distinct',
            'offset' => 'nullable|integer|min:0|max:999000',
        ]);
        $offset = (int) ($data['offset'] ?? 0);

        $positions = [];
        foreach (array_values($data['ids']) as $index => $id) {
            $positions[$id] = $offset + $index + 1;
            Category::where('id', $id)->update(['sort_order' => $positions[$id]]);
        }
        Cache::flush();

        return response()->json(['ok' => true, 'positions' => $positions]);
    }

    /** Status switch (a checkbox only posts when ticked) and the order number (blank → none). */
    private function visibility(Request $request): array
    {
        return [
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $request->filled('sort_order') ? (int) $request->input('sort_order') : null,
        ];
    }
}
