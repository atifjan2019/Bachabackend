<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CatalogController extends Controller
{
    public function categories(): JsonResponse
    {
        $categories = Category::query()
            ->whereNull('parent_id')
            ->withCount('products')
            ->with(['children' => function ($q) {
                $q->withCount('products')->orderBy('id');
            }])
            ->orderBy('id')
            ->get();

        return response()->json(['data' => $categories]);
    }

    public function products(Request $request): JsonResponse
    {
        $request->validate([
            'category' => 'nullable|string|max:100',
            'search' => 'nullable|string|max:255',
            'is_new' => 'nullable|boolean',
            'featured' => 'nullable|boolean',
            'best_seller' => 'nullable|boolean',
            'label' => 'nullable|string|max:100',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $perPage = (int) ($request->integer('per_page') ?: 20);
        $query = Product::query()->orderByDesc('id');

        if ($request->filled('category')) {
            $query->where('category', $request->string('category'));
        }

        if ($request->filled('search')) {
            $search = (string) $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (!is_null($request->query('is_new'))) {
            $query->where('is_new', $request->boolean('is_new'));
        }

        if (!is_null($request->query('featured'))) {
            $query->where('is_featured', $request->boolean('featured'));
        }

        if (!is_null($request->query('best_seller'))) {
            $query->where('is_best_seller', $request->boolean('best_seller'));
        }

        if ($request->filled('label')) {
            $query->where('label', $request->string('label'));
        }

        $data = $query->paginate($perPage)->appends($request->query());

        return response()->json($data);
    }

    public function product(string $slugOrId): JsonResponse
    {
        $product = Product::query()
            ->where('slug', $slugOrId)
            ->orWhere('id', $slugOrId)
            ->firstOrFail();

        return response()->json(['data' => $product]);
    }
}
