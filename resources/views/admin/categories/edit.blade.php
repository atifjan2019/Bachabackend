@extends('layouts.admin')
@section('title', 'Edit Category')
@section('content')

<div class="ph">
    <div>
        <h4>Edit Category</h4>
        <div class="ph-sub">Modifying {{ $category->name }}</div>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.categories.index') }}" class="btn btn-light"><i class="mdi mdi-arrow-left"></i> Back</a>
    </div>
</div>

<form id="catForm" action="{{ route('admin.categories.update', $category->id) }}" method="POST" enctype="multipart/form-data">
    @csrf @method('PUT')
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="bcard">
                <div class="bcard-body">
                    <div class="form-group">
                        <label class="form-label">Category Name <span class="req">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $category->name) }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">URL Slug</label>
                        <input type="text" name="slug" class="form-control" value="{{ old('slug', $category->slug) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4">{{ old('description', $category->description) }}</textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="bcard">
                <div class="bcard-body">
                    <div class="form-group">
                        <label class="form-label">Category Image</label>
                        <input type="file" name="image_file" class="form-control" accept="image/*" onchange="previewImg(this)">
                        <div id="img-preview" style="margin-top:8px;">
                            @if($category->image)
                                <img src="{{ $category->image }}" alt="Category image" style="max-height:140px; border-radius:6px; width:100%; object-fit:cover;">
                            @endif
                        </div>
                        <div style="display:flex; gap:8px; margin-top:6px;">
                            <span onclick="this.parentElement.nextElementSibling.classList.toggle('show'); this.textContent = this.parentElement.nextElementSibling.classList.contains('show') ? '− Hide URL' : '+ Enter URL manually'" style="font-size:11px; color:#999; cursor:pointer; text-decoration:underline;">+ Enter URL manually</span>
                            <span onclick="openMediaPicker('image', false)" style="font-size:11px; color:#e74c3c; cursor:pointer; text-decoration:underline;">📁 Select from Library</span>
                        </div>
                        <div style="display:none; margin-top:6px;" class="url-field">
                            <input type="text" name="image" class="form-control form-control-sm" value="{{ old('image', $category->image) }}">
                        </div>
                    </div>
                    <div class="form-group border-top pt-3 mt-3">
                        <label class="form-label">Meta Title (SEO)</label>
                        <input type="text" name="meta_title" class="form-control" value="{{ old('meta_title', $category->meta_title) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Meta Description (SEO)</label>
                        <textarea name="meta_description" class="form-control" rows="3">{{ old('meta_description', $category->meta_description) }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 mt-2">Update Category</button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
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
<script>initUploadProgress('catForm', '{{ route("admin.categories.index") }}');</script>
@include('admin.partials.media-picker')
@endsection
