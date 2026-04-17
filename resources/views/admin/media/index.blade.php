@extends('layouts.admin')
@section('title', 'Media Library')
@section('content')

<style>
.r2-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 12px;
    margin-top: 16px;
}
.r2-card {
    border: 1px solid #e5e5e5;
    border-radius: 8px;
    overflow: hidden;
    transition: all 0.15s ease;
    position: relative;
    background: #fafafa;
}
.r2-card:hover {
    border-color: #e74c3c;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}
.r2-card img {
    width: 100%;
    height: 130px;
    object-fit: cover;
    display: block;
    cursor: pointer;
}
.r2-card .r2-icon {
    width: 100%;
    height: 130px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #999;
    font-size: 2.5rem;
    cursor: pointer;
}
.r2-card .r2-meta {
    padding: 8px 10px;
    border-top: 1px solid #eee;
}
.r2-card .r2-name {
    font-size: 11px;
    color: #333;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.r2-card .r2-folder {
    font-size: 10px;
    color: #999;
    margin-top: 2px;
}
.r2-actions {
    display: flex;
    gap: 4px;
    margin-top: 6px;
}
.r2-actions button {
    flex: 1;
    padding: 4px 0;
    font-size: 11px;
    border: 1px solid #ddd;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.15s;
    background: #fff;
}
.r2-actions .copy-btn:hover { background: #2d3436; color: #fff; border-color: #2d3436; }
.r2-actions .del-btn { color: #e74c3c; }
.r2-actions .del-btn:hover { background: #e74c3c; color: #fff; border-color: #e74c3c; }
.copied-toast {
    position: fixed;
    bottom: 24px;
    right: 24px;
    background: #2d3436;
    color: #fff;
    padding: 10px 20px;
    border-radius: 8px;
    font-size: 13px;
    z-index: 9999;
    display: none;
    box-shadow: 0 4px 16px rgba(0,0,0,0.2);
}
.copied-toast.show {
    display: flex;
    align-items: center;
    gap: 8px;
}
.folder-filter {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    margin: 12px 0;
}
.folder-pill {
    padding: 4px 14px;
    border-radius: 20px;
    font-size: 12px;
    border: 1px solid #ddd;
    background: #fff;
    cursor: pointer;
    transition: all 0.15s;
}
.folder-pill:hover, .folder-pill.active {
    background: #2d3436;
    color: #fff;
    border-color: #2d3436;
}
</style>

<div class="ph">
    <div>
        <h4>Media Library</h4>
        <div class="ph-sub">{{ count($r2Files) }} files in cloud storage</div>
    </div>
    <div class="page-actions">
        <button class="btn btn-primary" onclick="document.getElementById('uploadModal').style.display='flex'">
            <i class="mdi mdi-upload"></i> Upload File
        </button>
    </div>
</div>

{{-- Upload Modal --}}
<div id="uploadModal" class="modal-shell">
    <div class="modal-panel">
        <div class="modal-head">
            <h6>Upload File</h6>
            <button onclick="document.getElementById('uploadModal').style.display='none'" class="tb-icon-btn" type="button">&times;</button>
        </div>
        <form id="mediaUploadForm" action="{{ route('admin.media.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label class="form-label">Select File (max 10MB)</label>
                <input type="file" name="file" class="form-control" required accept="image/*">
            </div>
            <button type="submit" class="btn btn-primary w-100">Upload to Cloud</button>
        </form>
    </div>
</div>

{{-- Copied Toast --}}
<div class="copied-toast" id="copiedToast">
    <i class="mdi mdi-check-circle"></i> <span id="toastMsg">URL copied to clipboard!</span>
</div>

@if(count($r2Files) > 0)
    @php
        $folders = collect($r2Files)->pluck('folder')->unique()->filter()->sort()->values();
    @endphp
    @if($folders->count() > 0)
    <div class="folder-filter">
        <span class="folder-pill active" onclick="filterFolder('all', this)">All</span>
        @foreach($folders as $folder)
            <span class="folder-pill" onclick="filterFolder('{{ $folder }}', this)">{{ $folder }}</span>
        @endforeach
    </div>
    @endif

    <p style="font-size:12px; color:#999; margin-bottom:4px;">Click image to copy URL · Use delete to remove from cloud</p>

    <div class="r2-grid" id="mediaGrid">
        @foreach($r2Files as $file)
            @php
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $isImage = in_array($ext, ['jpg','jpeg','png','gif','webp','svg']);
            @endphp
            <div class="r2-card" data-folder="{{ $file['folder'] }}" id="card-{{ md5($file['path']) }}">
                @if($isImage)
                    <img src="{{ $file['url'] }}" alt="{{ $file['name'] }}" loading="lazy" onclick="copyUrl('{{ $file['url'] }}')">
                @else
                    <div class="r2-icon" onclick="copyUrl('{{ $file['url'] }}')"><i class="mdi mdi-file-outline"></i></div>
                @endif
                <div class="r2-meta">
                    <div class="r2-name" title="{{ $file['name'] }}">{{ $file['name'] }}</div>
                    @if($file['folder'])
                        <div class="r2-folder">{{ $file['folder'] }}</div>
                    @endif
                    <div class="r2-actions">
                        <button class="copy-btn" onclick="copyUrl('{{ $file['url'] }}')"><i class="mdi mdi-content-copy"></i> Copy</button>
                        <button class="del-btn" onclick="deleteR2File('{{ $file['path'] }}', 'card-{{ md5($file['path']) }}')"><i class="mdi mdi-delete-outline"></i> Delete</button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="bcard">
        <div class="empty-state">
            <i class="mdi mdi-image-multiple-outline"></i>
            <strong>No files in cloud storage</strong>
            Upload product media, blog visuals, and brand assets here.
        </div>
    </div>
@endif

<script>
function copyUrl(url) {
    navigator.clipboard.writeText(url).then(function() {
        showToast('URL copied to clipboard!');
    });
}

function showToast(msg) {
    const toast = document.getElementById('copiedToast');
    document.getElementById('toastMsg').textContent = msg;
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 2000);
}

function filterFolder(folder, el) {
    document.querySelectorAll('.folder-pill').forEach(p => p.classList.remove('active'));
    el.classList.add('active');
    document.querySelectorAll('.r2-card').forEach(card => {
        card.style.display = (folder === 'all' || card.dataset.folder === folder) ? '' : 'none';
    });
}

function deleteR2File(path, cardId) {
    if (!confirm('Delete this file from cloud storage? This cannot be undone.')) return;

    fetch('{{ route("admin.media.api.deleteR2") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ path: path }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const card = document.getElementById(cardId);
            if (card) {
                card.style.transition = 'opacity 0.3s, transform 0.3s';
                card.style.opacity = '0';
                card.style.transform = 'scale(0.9)';
                setTimeout(() => card.remove(), 300);
            }
            showToast('File deleted from cloud');
        } else {
            alert('Failed to delete file');
        }
    })
    .catch(() => alert('Failed to delete file'));
}
</script>

@include('admin.partials.upload-progress')
<script>initUploadProgress('mediaUploadForm', '{{ route("admin.media.index") }}');</script>
@endsection
