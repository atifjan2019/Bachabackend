<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Support\MediaLibrary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    public function index()
    {
        $r2Files = MediaLibrary::files();
        $dbFiles = Media::orderBy('id', 'desc')->get();

        return view('admin.media.index', compact('r2Files', 'dbFiles'));
    }

    /**
     * AJAX: Return R2 files as JSON for the media picker modal.
     */
    public function apiList()
    {
        return response()->json(MediaLibrary::files());
    }

    public function store(Request $request)
    {
        $request->validate(['file' => 'required|file|max:10240']);
        $file = $request->file('file');
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();

        $diskName = config('filesystems.default');
        $path = 'media/'.$filename;
        $url = media_store($file, 'media', $filename);

        Media::create([
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $url,
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
        ]);

        MediaLibrary::forget();

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
            $diskName = config('filesystems.default');
            Storage::disk($diskName)->delete($path);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete'], 500);
        }

        // Also remove from DB if tracked
        $diskName = config('filesystems.default');
        $url = Storage::disk($diskName)->url($path);
        Media::where('file_path', $url)->delete();
        MediaLibrary::forget();

        return response()->json(['success' => true]);
    }

    public function destroy(string $id)
    {
        $media = Media::findOrFail($id);
        if (!empty($media->file_path)) {
            $diskName = config('filesystems.default');
            $parsedUrl = parse_url($media->file_path, PHP_URL_PATH);
            $relativePath = ltrim(str_replace('/storage/', '', $parsedUrl), '/');
            $diskUrl = config("filesystems.disks.{$diskName}.url");
            if ($diskName === 's3' && $diskUrl) {
                $relativePath = str_replace(rtrim($diskUrl, '/') . '/', '', $media->file_path);
            }
            if (!empty($relativePath)) {
                try { Storage::disk($diskName)->delete($relativePath); } catch (\Exception $e) {}
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
