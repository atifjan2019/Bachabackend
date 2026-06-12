@extends('layouts.admin')
@section('title', 'Edit Post')
@section('content')

<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<style>
#quill-editor { min-height: 300px; background: #fff; }
.ql-toolbar.ql-snow { border-radius: 6px 6px 0 0; }
.ql-container.ql-snow { border-radius: 0 0 6px 6px; }
</style>
<div class="ph">
    <div>
        <h4>Edit Post</h4>
        <div class="ph-sub">Update content, media, and publication status.</div>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.blog.index') }}" class="btn btn-light"><i class="mdi mdi-arrow-left"></i> Back</a>
    </div>
</div>

<form id="blogForm" action="{{ route('admin.blog.update', $post->id) }}" method="POST" enctype="multipart/form-data">
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
                        <div id="quill-editor">{!! old('content', $post->content) !!}</div>
                        <textarea name="content" id="contentField" style="display:none;">{{ old('content', $post->content) }}</textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Excerpt</label>
                        <textarea name="excerpt" class="form-control" rows="2" placeholder="Short summary shown on cards and used as a search snippet.">{{ old('excerpt', $post->excerpt) }}</textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <input type="text" name="category" class="form-control" value="{{ old('category', $post->category) }}" placeholder="e.g. Heritage, Styling, Collections">
                    </div>
                </div>
            </div>

            <div class="bcard">
                <div class="bcard-head"><span class="bcard-title">SEO Settings</span></div>
                <div class="bcard-body">
                    <div class="form-group">
                        <label class="form-label">Meta Title</label>
                        <input type="text" name="meta_title" class="form-control" value="{{ old('meta_title', $post->meta_title) }}" placeholder="Defaults to the post title">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Meta Description</label>
                        <textarea name="meta_description" class="form-control" rows="2" placeholder="~155 characters shown in search results">{{ old('meta_description', $post->meta_description) }}</textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Meta Keywords</label>
                        <input type="text" name="meta_keywords" class="form-control" value="{{ old('meta_keywords', $post->meta_keywords) }}" placeholder="comma, separated, keywords">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Social Share Image (OG Image)</label>
                        <div id="og-preview" style="margin-bottom:8px; {{ old('og_image', $post->og_image) ? '' : 'display:none;' }}">
                            <img src="{{ old('og_image', $post->og_image) }}" alt="OG preview" style="max-height:120px; border-radius:6px;">
                        </div>
                        <span class="btn-library" onclick="openMediaPicker('og_image', false)"><i class="mdi mdi-folder-image"></i> Select from Library</span>
                        <input type="hidden" name="og_image" value="{{ old('og_image', $post->og_image) }}">
                        <div class="form-hint">Used when the post is shared on social media. Defaults to the featured image.</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Canonical URL</label>
                        <input type="text" name="canonical_url" class="form-control" value="{{ old('canonical_url', $post->canonical_url) }}" placeholder="https://bachastylo.com/blogs/...">
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="bcard">
                <div class="bcard-body">
                    <div class="form-group">
                        <label class="form-label">Featured Image</label>
                        <input type="file" name="image_file" class="form-control" accept="image/*" onchange="previewImg(this)">
                        <div id="img-preview" style="margin-top:8px;">
                            @if($post->image)
                                <img src="{{ $post->image }}" alt="Featured image" style="max-height:140px; border-radius:6px; width:100%; object-fit:cover;">
                            @endif
                        </div>
                        <span class="btn-library" onclick="openMediaPicker('image', false)"><i class="mdi mdi-folder-image"></i> Select from Library</span>
                        <input type="hidden" name="image" value="{{ old('image', $post->image) }}">
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
            <div class="d-grid mt-2">
                <button type="button" onclick="document.getElementById('deleteForm-{{ $post->id }}').submit()" class="btn btn-outline-danger">Delete Post</button>
            </div>
        </div>
    </div>
</form>

<form id="deleteForm-{{ $post->id }}" action="{{ route('admin.blog.destroy', $post->id) }}" method="POST" onsubmit="return confirm('Delete this post?')">
    @csrf @method('DELETE')
</form>

<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
var quill = new Quill('#quill-editor', {
    theme: 'snow',
    modules: {
        toolbar: [
            [{ 'header': [2, 3, false] }],
            ['bold', 'italic', 'underline', 'strike'],
            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
            ['link', 'image', 'video'],
            ['clean']
        ]
    },
    placeholder: 'Write blog content...'
});

document.getElementById('blogForm').addEventListener('submit', function(e) {
    document.getElementById('contentField').value = quill.root.innerHTML;
});

document.getElementById('blogForm').addEventListener('formdata', function(e) {
    e.formData.set('content', quill.root.innerHTML);
});

function previewImg(input) {
    const preview = document.getElementById('img-preview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview" style="max-height:140px; border-radius:6px; width:100%; object-fit:cover;">';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

@include('admin.partials.upload-progress')
<script>initUploadProgress('blogForm', '{{ route("admin.blog.index") }}');</script>
@include('admin.partials.media-picker')
@endsection
