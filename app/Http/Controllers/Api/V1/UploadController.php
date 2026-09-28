<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,gif,webp,avif,pdf|max:10240',
            'folder' => 'nullable|string|in:products,receipts',
        ]);

        $folder = $request->input('folder', 'products');
        $file = $request->file('file');
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $disk = config('filesystems.default');
        $path = $file->storeAs($folder, $filename, $disk);

        $url = Storage::disk($disk)->url($path);

        return response()->json([
            'url' => $url,
            'path' => $path,
        ]);
    }
}
