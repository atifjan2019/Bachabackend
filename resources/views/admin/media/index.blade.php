@extends('layouts.admin')
@section('title', 'Media Library')
@section('content')

<div class="ph">
    <div>
        <h4>Media Library</h4>
        <div class="ph-sub">{{ $files->total() }} files</div>
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
        <form action="{{ route('admin.media.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label class="form-label">Select File (max 10MB)</label>
                <input type="file" name="file" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Upload</button>
        </form>
    </div>
</div>

@if($files->count() > 0)
<div class="media-grid">
    @foreach($files as $file)
        <div class="media-card">
            <div class="media-preview">
                @if(str_starts_with($file->file_type ?? '', 'image/'))
                    <img src="{{ $file->file_path }}" alt="{{ $file->file_name }}">
                @else
                    <i class="mdi mdi-file-outline" style="font-size:2.1rem;color:var(--t3);"></i>
                @endif
            </div>
            <div class="media-meta">
                <div class="media-name">{{ $file->file_name }}</div>
                <div class="media-size">{{ $file->file_size ? round($file->file_size/1024, 1).' KB' : '' }}</div>
                <form action="{{ route('admin.media.destroy', $file->id) }}" method="POST" class="mt-3" onsubmit="return confirm('Delete?')">
                    @csrf @method('DELETE')
                    <button class="btn-ghost-del w-100" type="submit">Delete</button>
                </form>
            </div>
        </div>
    @endforeach
</div>
@else
<div class="bcard">
    <div class="empty-state">
        <i class="mdi mdi-image-multiple-outline"></i>
        <strong>No files uploaded yet</strong>
        Upload product media, blog visuals, and brand assets here.
    </div>
</div>
@endif

@if($files->hasPages())
<div class="mt-3">{{ $files->links() }}</div>
@endif
@endsection
