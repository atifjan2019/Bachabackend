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
        $data = Cache::remember('api:categories', 300, function () {
            return Category::query()
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'description', 'image', 'meta_title', 'meta_description']);
        });

        return response()->json(['data' => $data])
            ->header('Cache-Control', 'public, max-age=60, s-maxage=300');
    }

    public function products(Request $request): JsonResponse
    {
        $request->validate([
            'category' => 'nullable|string|max:100',
            'search' => 'nullable|string|max:255',
            'is_new' => 'nullable|boolean',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $cacheKey = 'api:products:' . md5(json_encode($request->query()));

        $data = Cache::remember($cacheKey, 300, function () use ($request) {
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

            return $query->paginate($perPage)->appends($request->query());
        });

        return response()->json($data)
            ->header('Cache-Control', 'public, max-age=60, s-maxage=300');
    }

    public function product(string $slugOrId): JsonResponse
    {
        $data = Cache::remember('api:product:' . $slugOrId, 300, function () use ($slugOrId) {
            return Product::query()
                ->where('slug', $slugOrId)
                ->orWhere('id', $slugOrId)
                ->firstOrFail();
        });

        return response()->json(['data' => $data])
            ->header('Cache-Control', 'public, max-age=60, s-maxage=300');
    }
}
