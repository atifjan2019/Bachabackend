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
        // List all files from R2 storage
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
                    'size' => null, // Skip size check for performance
                ];
            }
        } catch (\Exception $e) {
            // R2 not configured - fall back to empty
        }

        // Also get locally uploaded media from DB
        $dbFiles = Media::orderBy('id', 'desc')->get();

        return view('admin.media.index', compact('r2Files', 'dbFiles'));
    }

    public function store(Request $request)
    {
        $request->validate(['file' => 'required|file|max:10240']);
        $file = $request->file('file');
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();

        // Upload to R2
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

    public function destroy(string $id)
    {
        $media = Media::findOrFail($id);

        // Try to delete from R2
        if (!empty($media->file_path)) {
            $baseUrl = rtrim(env('AWS_URL', ''), '/');
            $relativePath = str_replace($baseUrl . '/', '', $media->file_path);
            if (!empty($relativePath)) {
                try {
                    Storage::disk('s3')->delete($relativePath);
                } catch (\Exception $e) {
                    // Ignore if R2 delete fails
                }
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
