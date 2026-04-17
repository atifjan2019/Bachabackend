@extends('layouts.admin')
@section('title', 'Edit Post')
@section('content')

<div class="ph">
    <div>
        <h4>Edit Post</h4>
        <div class="ph-sub">Update content, media, and publication status.</div>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.blog.index') }}" class="btn btn-light"><i class="mdi mdi-arrow-left"></i> Back</a>
    </div>
</div>

<form action="{{ route('admin.blog.update', $post->id) }}" method="POST">
    @csrf @method('PUT')
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="bcard">
                <div class="bcard-body">
                    <div class="form-group">
                        <label class="form-label">Title <span class="req">*</span></label>
                        <input type="text" name="title" class="form-control" value="{{ old('title', $post->title) }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Slug</label>
                        <input type="text" name="slug" class="form-control" value="{{ old('slug', $post->slug) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Content</label>
                        <textarea name="content" class="form-control" rows="12">{{ old('content', $post->content) }}</textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="bcard">
                <div class="bcard-body">
                    <div class="form-group">
                        <label class="form-label">Featured Image URL</label>
                        <input type="text" name="image" class="form-control" value="{{ old('image', $post->image) }}">
                    </div>
                    <div class="form-group">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="status" id="status" value="1" {{ $post->status ? 'checked' : '' }}>
                            <label class="form-check-label" for="status">Published</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Update Post</button>
                </div>
            </div>
            <form action="{{ route('admin.blog.destroy', $post->id) }}" method="POST" onsubmit="return confirm('Delete this post?')">
                @csrf @method('DELETE')
                <div class="d-grid mt-2">
                    <button type="submit" class="btn btn-outline-danger">Delete Post</button>
                </div>
            </form>
        </div>
    </div>
</form>
@endsection
