@extends('layouts.admin')
@section('title', 'New Blog Post')
@section('content')

<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<style>
#quill-editor { min-height: 300px; background: #fff; }
.ql-toolbar.ql-snow { border-radius: 6px 6px 0 0; }
.ql-container.ql-snow { border-radius: 0 0 6px 6px; }
</style>
<div class="ph">
    <div>
        <h4>New Blog Post</h4>
        <div class="ph-sub">Create editorial content for the storefront and search engines.</div>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.blog.index') }}" class="btn btn-light"><i class="mdi mdi-arrow-left"></i> Back</a>
    </div>
</div>

<form id="blogForm" action="{{ route('admin.blog.store') }}" method="POST" enctype="multipart/form-data">
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
                        <div id="quill-editor">{!! old('content') !!}</div>
                        <textarea name="content" id="contentField" style="display:none;">{{ old('content') }}</textarea>
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
                        <div id="img-preview" style="margin-top:8px; display:none;"><img src="" alt="Preview" style="max-height:140px; border-radius:6px; width:100%; object-fit:cover;"></div>
                        <span class="btn-library" onclick="openMediaPicker('image', false)"><i class="mdi mdi-folder-image"></i> Select from Library</span>
                        <input type="hidden" name="image" value="{{ old('image') }}">
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

// For AJAX / FormData integration fallback
document.getElementById('blogForm').addEventListener('formdata', function(e) {
    e.formData.set('content', quill.root.innerHTML);
});

function previewImg(input) {
    const preview = document.getElementById('img-preview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.style.display = 'block';
            preview.querySelector('img').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

@include('admin.partials.upload-progress')
<script>initUploadProgress('blogForm', '{{ route("admin.blog.index") }}');</script>
@include('admin.partials.media-picker')

@endsection
