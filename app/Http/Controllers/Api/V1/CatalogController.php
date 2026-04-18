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
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'description', 'image', 'meta_title', 'meta_description']);
            
        $data = $categories->map(function ($category) {
            if ($category->image) {
                if (str_contains($category->image, 'unsplash.com') || str_contains($category->image, 'cloudinary.com') || str_starts_with($category->image, 'data:')) {
                    // skip external
                } else {
                    $parsed = parse_url($category->image, PHP_URL_PATH);
                    $category->image = url($parsed);
                }
            }
            return $category;
        });

        return response()->json(['data' => $data]);
    }

    public function products(Request $request): JsonResponse
    {
        $request->validate([
            'category' => 'nullable|string|max:100',
            'search' => 'nullable|string|max:255',
            'is_new' => 'nullable|boolean',
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

        $data = $query->paginate($perPage)->appends($request->query())->through(function ($product) {
            if ($product->image) {
                if (str_contains($product->image, 'unsplash.com') || str_contains($product->image, 'cloudinary.com') || str_starts_with($product->image, 'data:')) {
                    // Skip external
                } else {
                    $parsed = parse_url($product->image, PHP_URL_PATH);
                    $product->image = url($parsed);
                }
            }
            
            if (is_array($product->gallery)) {
                $product->gallery = array_map(function ($url) {
                    if (str_contains($url, 'unsplash.com') || str_contains($url, 'cloudinary.com') || str_starts_with($url, 'data:')) return $url;
                    $parsed = parse_url($url, PHP_URL_PATH);
                    return $parsed ? url($parsed) : $url;
                }, $product->gallery);
            }

            return $product;
        });

        return response()->json($data);
    }

    public function product(string $slugOrId): JsonResponse
    {
        $product = Product::query()
            ->where('slug', $slugOrId)
            ->orWhere('id', $slugOrId)
            ->firstOrFail();

        if ($product->image) {
            if (str_contains($product->image, 'unsplash.com') || str_contains($product->image, 'cloudinary.com')) {
                // Skip external
            } else {
                $parsed = parse_url($product->image, PHP_URL_PATH);
                $product->image = url($parsed);
            }
        }
        
        if (is_array($product->gallery)) {
            $product->gallery = array_map(function ($url) {
                if (str_contains($url, 'unsplash.com') || str_contains($url, 'cloudinary.com')) return $url;
                $parsed = parse_url($url, PHP_URL_PATH);
                return $parsed ? url($parsed) : $url;
            }, $product->gallery);
        }
        $data = $product;

        return response()->json(['data' => $data]);
    }
}
