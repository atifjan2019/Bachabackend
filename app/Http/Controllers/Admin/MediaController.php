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
        $files = Media::orderBy('id', 'desc')->paginate(30);
        return view('admin.media.index', compact('files'));
    }

    public function store(Request $request)
    {
        $request->validate(['file' => 'required|file|max:10240']);
        $file = $request->file('file');
        $path = $file->store('media', 'public');
        Media::create([
            'file_name' => $file->getClientOriginalName(),
            'file_path' => '/storage/' . $path,
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
        ]);
        return redirect()->route('admin.media.index')->with('success', 'File uploaded.');
    }

    public function destroy(string $id)
    {
        $media = Media::findOrFail($id);

        if (!empty($media->file_path)) {
            $relativePath = Str::after($media->file_path, '/storage/');
            if (!empty($relativePath)) {
                Storage::disk('public')->delete($relativePath);
            }
        }

        $media->delete();
        return redirect()->route('admin.media.index')->with('success', 'File deleted.');
    }

    public function create() { return view('admin.media.index', ['files' => Media::paginate(30)]); }
    public function show(string $id) { abort(404); }
    public function edit(string $id) { abort(404); }
    public function update(Request $r, string $id) { abort(404); }
}
