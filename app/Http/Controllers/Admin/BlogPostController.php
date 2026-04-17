<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
        $request->validate(['title' => 'required|string|max:255']);
        $data = $request->except('_token');
        $data['slug'] = $data['slug'] ?? Str::slug($data['title']);
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
        $request->validate(['title' => 'required|string|max:255']);
        $post = BlogPost::findOrFail($id);
        $data = $request->except(['_token', '_method']);
        $data['slug'] = $data['slug'] ?? Str::slug($data['title']);
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
