@extends('layouts.admin')
@section('title', 'Edit Product')
@section('content')

<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">

<style>
.upload-overlay {
    display: none;
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.5);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}
.upload-overlay.active { display: flex; }
.upload-card {
    background: #fff;
    border-radius: 12px;
    padding: 32px 40px;
    min-width: 380px;
    text-align: center;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}
.upload-card h5 { margin: 0 0 6px; font-size: 18px; }
.upload-card .upload-status { font-size: 13px; color: #666; margin-bottom: 16px; }
.progress-track {
    width: 100%;
    height: 8px;
    background: #e9ecef;
    border-radius: 8px;
    overflow: hidden;
    margin-bottom: 10px;
}
.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #e74c3c, #f39c12);
    border-radius: 8px;
    width: 0%;
    transition: width 0.2s ease;
}
.upload-pct { font-size: 24px; font-weight: 700; color: #333; }
.url-toggle {
    display: inline-block;
    font-size: 11px;
    color: #999;
    cursor: pointer;
    margin-top: 6px;
    text-decoration: underline;
    transition: color 0.15s;
}
.url-toggle:hover { color: #555; }
.url-field { display: none; margin-top: 6px; }
.url-field.show { display: block; }
#quill-editor { min-height: 180px; background: #fff; }
.ql-toolbar.ql-snow { border-radius: 6px 6px 0 0; }
.ql-container.ql-snow { border-radius: 0 0 6px 6px; }
</style>

<div class="ph">
    <div>
        <h4>Edit Product</h4>
        <div class="ph-sub">Modifying {{ $product->name }}</div>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.products.index') }}" class="btn btn-light"><i class="mdi mdi-arrow-left"></i> Back</a>
    </div>
</div>

<div class="upload-overlay" id="uploadOverlay">
    <div class="upload-card">
        <h5>Uploading Images...</h5>
        <div class="upload-status" id="uploadStatus">Preparing files...</div>
        <div class="progress-track">
            <div class="progress-fill" id="uploadProgressBar"></div>
        </div>
        <div class="upload-pct" id="uploadPct">0%</div>
    </div>
</div>

<form id="productForm" action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
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
                        <div id="quill-editor">{!! old('description', $product->description) !!}</div>
                        <textarea name="description" id="descriptionField" style="display:none;">{{ old('description', $product->description) }}</textarea>
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
                            <span class="url-toggle" onclick="openMediaPicker('image', false)" style="color:#e74c3c;">📁 Select from Library</span>
                            <div id="preview-main" class="preview-box" style="margin-top:8px;">
                                @if($product->image)
                                    <img src="{{ $product->image }}" alt="Product image" style="max-height:160px; border-radius:6px;">
                                @endif
                            </div>
                            <input type="hidden" name="image" value="{{ old('image', $product->image) }}">
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
                            <span class="url-toggle" onclick="openMediaPicker('gallery', true)" style="color:#e74c3c;">📁 Select from Library</span>
                            <input type="hidden" name="gallery" value="{{ old('gallery', is_array($product->gallery) ? json_encode($product->gallery) : $product->gallery) }}">
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

            <button type="submit" class="btn btn-primary w-100 btn-lg" id="submitBtn">
                <i class="mdi mdi-content-save-outline"></i> Update Product
            </button>
            </div>
        </div>
    </div>
</form>

<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
// WYSIWYG Editor
var quill = new Quill('#quill-editor', {
    theme: 'snow',
    modules: {
        toolbar: [
            [{ 'header': [2, 3, false] }],
            ['bold', 'italic', 'underline'],
            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
            ['link'],
            ['clean']
        ]
    },
    placeholder: 'Write product description...'
});

function toggleUrl(el) {
    const field = el.nextElementSibling;
    field.classList.toggle('show');
    el.textContent = field.classList.contains('show') ? '− Hide URL field' : '+ Enter URL manually';
}

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

// AJAX form submission with upload progress
document.getElementById('productForm').addEventListener('submit', function(e) {
    document.getElementById('descriptionField').value = quill.root.innerHTML;

    const form = this;
    const fileInputs = form.querySelectorAll('input[type="file"]');
    let hasFiles = false;
    fileInputs.forEach(inp => { if (inp.files && inp.files.length > 0) hasFiles = true; });

    if (!hasFiles) return;

    e.preventDefault();

    const overlay = document.getElementById('uploadOverlay');
    const progressBar = document.getElementById('uploadProgressBar');
    const pctText = document.getElementById('uploadPct');
    const statusText = document.getElementById('uploadStatus');
    const submitBtn = document.getElementById('submitBtn');

    overlay.classList.add('active');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Uploading...';

    const formData = new FormData(form);
    formData.set('description', quill.root.innerHTML);

    const xhr = new XMLHttpRequest();

    xhr.upload.addEventListener('progress', function(e) {
        if (e.lengthComputable) {
            const pct = Math.round((e.loaded / e.total) * 100);
            progressBar.style.width = pct + '%';
            pctText.textContent = pct + '%';
            if (pct < 30) statusText.textContent = 'Uploading images...';
            else if (pct < 70) statusText.textContent = 'Transferring to cloud storage...';
            else if (pct < 100) statusText.textContent = 'Almost done...';
            else statusText.textContent = 'Processing...';
        }
    });

    xhr.addEventListener('load', function() {
        if (xhr.status >= 200 && xhr.status < 400) {
            statusText.textContent = 'Done! Redirecting...';
            pctText.textContent = '100%';
            progressBar.style.width = '100%';
            window.location.href = xhr.responseURL || '{{ route("admin.products.index") }}';
        } else {
            overlay.classList.remove('active');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="mdi mdi-content-save-outline"></i> Update Product';
            alert('Upload failed. Please try again.');
        }
    });

    xhr.addEventListener('error', function() {
        overlay.classList.remove('active');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="mdi mdi-content-save-outline"></i> Update Product';
        alert('Upload failed. Please check your connection.');
    });

    xhr.open('POST', form.action);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.send(formData);
});

document.getElementById('productForm').addEventListener('formdata', function(e) {
    e.formData.set('description', quill.root.innerHTML);
});
</script>
@include('admin.partials.media-picker')
@endsection
