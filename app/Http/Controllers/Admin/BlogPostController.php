<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class BlogPostController extends Controller
{
    public function index()
    {
        $posts = BlogPost::orderBy('id', 'desc')->paginate(20);
        return view('admin.blog.index', compact('posts'));
    }

    public function create()
    {
        return view('admin.blog.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'image_file' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp|max:10240',
        ]);
        $data = $request->except(['_token', 'image_file']);
        $data['slug'] = $data['slug'] ?? Str::slug($data['title']);
        $data['status'] = $request->boolean('status');

        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $disk = env('FILESYSTEM_DISK', 'public');
            $file->storeAs('blog', $filename, $disk);
            $data['image'] = Storage::disk($disk)->url('blog/' . $filename);
        }

        BlogPost::create($data);
        return redirect()->route('admin.blog.index')->with('success', 'Blog post created.');
    }

    public function edit(string $id)
    {
        $post = BlogPost::findOrFail($id);
        return view('admin.blog.edit', compact('post'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'image_file' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp|max:10240',
        ]);
        $post = BlogPost::findOrFail($id);
        $data = $request->except(['_token', '_method', 'image_file']);
        $data['slug'] = $data['slug'] ?? Str::slug($data['title']);
        $data['status'] = $request->boolean('status');

        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $disk = env('FILESYSTEM_DISK', 'public');
            $file->storeAs('blog', $filename, $disk);
            $data['image'] = Storage::disk($disk)->url('blog/' . $filename);
        }

        $post->update($data);
        return redirect()->route('admin.blog.index')->with('success', 'Blog post updated.');
    }

    public function destroy(string $id)
    {
        BlogPost::findOrFail($id)->delete();
        return redirect()->route('admin.blog.index')->with('success', 'Post deleted.');
    }

    public function show(string $id) { return redirect()->route('admin.blog.edit', $id); }
}
