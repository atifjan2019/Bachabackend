@extends('layouts.admin')
@section('title', 'Edit Product')
@section('content')

<div class="ph">
    <div>
        <h4>Edit Product</h4>
        <div class="ph-sub">Modifying {{ $product->name }}</div>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.products.index') }}" class="btn btn-light"><i class="mdi mdi-arrow-left"></i> Back</a>
    </div>
</div>

<form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
    @csrf @method('PUT')
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="bcard">
                <div class="bcard-head"><span class="bcard-title">Product Details</span></div>
                <div class="bcard-body">
                    <div class="form-group">
                        <label class="form-label">Product Name <span class="req">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">URL Slug</label>
                        <input type="text" name="slug" class="form-control" value="{{ old('slug', $product->slug) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="6">{{ old('description', $product->description) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="bcard">
                <div class="bcard-head"><span class="bcard-title">Media</span></div>
                <div class="bcard-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Main Image</label>
                            <input type="file" name="image_file" class="form-control" accept="image/*" onchange="previewImg(this, 'preview-main')">
                            <div id="preview-main" class="preview-box" style="margin-top:8px;">
                                @if($product->image)
                                    <img src="{{ $product->image }}" alt="Product image" style="max-height:160px; border-radius:6px;">
                                @endif
                            </div>
                            <div style="margin-top:6px;">
                                <small class="text-muted">Or paste URL:</small>
                                <input type="text" name="image" class="form-control form-control-sm mt-1" value="{{ old('image', $product->image) }}">
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Lifestyle Image</label>
                            <input type="file" name="lifestyle_file" class="form-control" accept="image/*" onchange="previewImg(this, 'preview-lifestyle')">
                            <div id="preview-lifestyle" class="preview-box" style="margin-top:8px;">
                                @if($product->lifestyle)
                                    <img src="{{ $product->lifestyle }}" alt="Lifestyle image" style="max-height:160px; border-radius:6px;">
                                @endif
                            </div>
                            <div style="margin-top:6px;">
                                <small class="text-muted">Or paste URL:</small>
                                <input type="text" name="lifestyle" class="form-control form-control-sm mt-1" value="{{ old('lifestyle', $product->lifestyle) }}">
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Gallery Images</label>
                            <input type="file" name="gallery_files[]" class="form-control" accept="image/*" multiple onchange="previewGallery(this, 'preview-gallery')">
                            <div id="preview-gallery" style="display:flex; gap:8px; flex-wrap:wrap; margin-top:8px;">
                                @php
                                    $galleryUrls = is_array($product->gallery) ? $product->gallery : json_decode($product->gallery ?? '[]', true) ?? [];
                                @endphp
                                @foreach($galleryUrls as $gUrl)
                                    <img src="{{ $gUrl }}" alt="Gallery" style="max-height:100px; border-radius:6px; border:1px solid #ddd;">
                                @endforeach
                            </div>
                            <div style="margin-top:6px;">
                                <small class="text-muted">Or paste URLs (JSON array):</small>
                                <textarea name="gallery" class="form-control form-control-sm mt-1" rows="2">{{ old('gallery', is_array($product->gallery) ? json_encode($product->gallery) : $product->gallery) }}</textarea>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Video URL</label>
                            <input type="text" name="video_url" class="form-control" value="{{ old('video_url', $product->video_url) }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="section-stack">
            <div class="bcard">
                <div class="bcard-head"><span class="bcard-title">Pricing</span></div>
                <div class="bcard-body">
                    <div class="form-group">
                        <label class="form-label">Sale Price (Rs.) <span class="req">*</span></label>
                        <input type="text" name="price" class="form-control" value="{{ old('price', $product->price) }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Original Price (Rs.)</label>
                        <input type="text" name="original_price" class="form-control" value="{{ old('original_price', $product->original_price) }}">
                    </div>
                </div>
            </div>

            <div class="bcard">
                <div class="bcard-head"><span class="bcard-title">Organisation</span></div>
                <div class="bcard-body">
                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select">
                            <option value="">— None —</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->name }}" {{ old('category', $product->category) == $cat->name ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Sizes <span class="form-hint">(JSON array)</span></label>
                        <input type="text" name="sizes" class="form-control" value="{{ old('sizes', is_array($product->sizes) ? json_encode($product->sizes) : $product->sizes) }}">
                    </div>
                    <div class="form-group">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_new" id="is_new" value="1" {{ old('is_new', $product->is_new) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_new">Mark as New Arrival</label>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 btn-lg">
                <i class="mdi mdi-content-save-outline"></i> Update Product
            </button>
            </div>
        </div>
    </div>
</form>

<script>
function previewImg(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview" style="max-height:160px; border-radius:6px;">';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
function previewGallery(input, containerId) {
    const container = document.getElementById(containerId);
    container.innerHTML = '';
    if (input.files) {
        Array.from(input.files).forEach(file => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.style.cssText = 'max-height:100px; border-radius:6px; border:1px solid #ddd;';
                container.appendChild(img);
            };
            reader.readAsDataURL(file);
        });
    }
}
</script>
@endsection
