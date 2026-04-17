<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    public function index()
    {
        $r2Files = [];
        try {
            $disk = Storage::disk('s3');
            $allFiles = $disk->allFiles();
            $baseUrl = rtrim(env('AWS_URL', ''), '/');

            foreach ($allFiles as $path) {
                $r2Files[] = [
                    'path' => $path,
                    'url' => $baseUrl . '/' . $path,
                    'name' => basename($path),
                    'folder' => dirname($path) === '.' ? '' : dirname($path),
                ];
            }
        } catch (\Exception $e) {}

        $dbFiles = Media::orderBy('id', 'desc')->get();

        return view('admin.media.index', compact('r2Files', 'dbFiles'));
    }

    /**
     * AJAX: Return R2 files as JSON for the media picker modal.
     */
    public function apiList()
    {
        $r2Files = [];
        try {
            $disk = Storage::disk('s3');
            $allFiles = $disk->allFiles();
            $baseUrl = rtrim(env('AWS_URL', ''), '/');

            foreach ($allFiles as $path) {
                $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg','jpeg','png','gif','webp','svg'])) {
                    $r2Files[] = [
                        'path' => $path,
                        'url' => $baseUrl . '/' . $path,
                        'name' => basename($path),
                        'folder' => dirname($path) === '.' ? '' : dirname($path),
                    ];
                }
            }
        } catch (\Exception $e) {}

        return response()->json($r2Files);
    }

    public function store(Request $request)
    {
        $request->validate(['file' => 'required|file|max:10240']);
        $file = $request->file('file');
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();

        $path = $file->storeAs('media', $filename, 's3');
        $url = rtrim(env('AWS_URL', ''), '/') . '/' . $path;

        Media::create([
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $url,
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
        ]);

        return redirect()->route('admin.media.index')->with('success', 'File uploaded to cloud storage.');
    }

    /**
     * Delete a file from R2 by its path (AJAX).
     */
    public function deleteR2(Request $request)
    {
        $request->validate(['path' => 'required|string']);
        $path = $request->input('path');

        try {
            Storage::disk('s3')->delete($path);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete'], 500);
        }

        // Also remove from DB if tracked
        $baseUrl = rtrim(env('AWS_URL', ''), '/');
        $url = $baseUrl . '/' . $path;
        Media::where('file_path', $url)->delete();

        return response()->json(['success' => true]);
    }

    public function destroy(string $id)
    {
        $media = Media::findOrFail($id);
        if (!empty($media->file_path)) {
            $baseUrl = rtrim(env('AWS_URL', ''), '/');
            $relativePath = str_replace($baseUrl . '/', '', $media->file_path);
            if (!empty($relativePath)) {
                try { Storage::disk('s3')->delete($relativePath); } catch (\Exception $e) {}
            }
        }
        $media->delete();
        return redirect()->route('admin.media.index')->with('success', 'File deleted.');
    }

    public function create() { return redirect()->route('admin.media.index'); }
    public function show(string $id) { abort(404); }
    public function edit(string $id) { abort(404); }
    public function update(Request $r, string $id) { abort(404); }
}
