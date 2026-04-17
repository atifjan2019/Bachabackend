@extends('layouts.admin')
@section('title', 'New Blog Post')
@section('content')

<div class="ph">
    <div>
        <h4>New Blog Post</h4>
        <div class="ph-sub">Create editorial content for the storefront and search engines.</div>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.blog.index') }}" class="btn btn-light"><i class="mdi mdi-arrow-left"></i> Back</a>
    </div>
</div>

<form action="{{ route('admin.blog.store') }}" method="POST">
    @csrf
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="bcard">
                <div class="bcard-body">
                    <div class="form-group">
                        <label class="form-label">Title <span class="req">*</span></label>
                        <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Slug</label>
                        <input type="text" name="slug" class="form-control" value="{{ old('slug') }}" placeholder="Auto-generated">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Content</label>
                        <textarea name="content" class="form-control" rows="12">{{ old('content') }}</textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="bcard">
                <div class="bcard-body">
                    <div class="form-group">
                        <label class="form-label">Featured Image URL</label>
                        <input type="text" name="image" class="form-control" value="{{ old('image') }}" placeholder="https://...">
                    </div>
                    <div class="form-group">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="status" id="status" value="1" checked>
                            <label class="form-check-label" for="status">Published</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Save Post</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
