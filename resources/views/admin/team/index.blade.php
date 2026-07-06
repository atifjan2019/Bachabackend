@extends('layouts.admin')
@section('title', 'Team & About')
@section('content')

<div class="ph">
    <div>
        <h4>Team &amp; About</h4>
        <div class="ph-sub">Manage the team structure cards and the founder section shown on the storefront About page.</div>
    </div>
    <div class="ph-action">
        <a href="{{ route('admin.team.create') }}" class="btn btn-primary"><i class="mdi mdi-account-plus"></i> Add Team Member</a>
    </div>
</div>

{{-- ─── TEAM STRUCTURE CARDS ─────────────────────────────── --}}
<div class="bcard">
    <div class="bcard-head"><span class="bcard-title">Team Structure</span></div>
    <div class="table-wrap">
        <table class="table table-stack mb-0">
            <thead>
                <tr>
                    <th>ORDER</th>
                    <th>MEMBER</th>
                    <th>ICON</th>
                    <th>STATUS</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($members as $member)
                <tr>
                    <td data-label="Order" class="text-muted">{{ $member->sort_order }}</td>
                    <td data-label="Member">
                        <div class="entity">
                            <div class="entity-thumb">
                                @if($member->image_url)
                                    <img src="{{ $member->image_url }}" alt="{{ $member->title }}">
                                @else
                                    <i class="mdi mdi-account-outline"></i>
                                @endif
                            </div>
                            <div class="entity-copy">
                                <div class="entity-title">{{ $member->title }}</div>
                                <div class="entity-meta">{{ $member->subtitle }}</div>
                            </div>
                        </div>
                    </td>
                    <td data-label="Icon"><span class="soft-badge">{{ $member->icon }}</span></td>
                    <td data-label="Status">
                        @if($member->is_active)
                            <span class="status-badge" style="background:#f0fdf4;color:#15803d;">Active</span>
                        @else
                            <span class="status-badge" style="background:#fef2f2;color:#dc2626;">Hidden</span>
                        @endif
                    </td>
                    <td data-label="Actions">
                        <div class="action-group">
                            <a href="{{ route('admin.team.edit', $member->id) }}" class="btn btn-sm btn-light btn-icon" title="Edit"><i class="mdi mdi-pencil-outline"></i></a>
                            <form action="{{ route('admin.team.destroy', $member->id) }}" method="POST" onsubmit="return confirm('Remove &quot;{{ $member->title }}&quot;?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-ghost-del" title="Delete"><i class="mdi mdi-trash-can-outline"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="empty-state">
                        <i class="mdi mdi-account-group-outline"></i>
                        <strong>No team members</strong>
                        Add cards to build the "Our Team Structure" section.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ─── FOUNDER SECTION ──────────────────────────────────── --}}
<div class="bcard">
    <div class="bcard-head"><span class="bcard-title">Founder Section</span></div>
    <div class="card-body">
        <form action="{{ route('admin.team.founder.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="fieldset-grid">
                <div class="field-span-6 form-group">
                    <label class="form-label">Founder Name <span class="text-danger">*</span></label>
                    <input type="text" name="about_founder_name" class="form-control" required value="{{ old('about_founder_name', $founder['about_founder_name'] ?? '') }}" placeholder="e.g. Muhammad Ali Shah Bacha">
                </div>
                <div class="field-span-6 form-group">
                    <label class="form-label">Role / Title</label>
                    <input type="text" name="about_founder_role" class="form-control" value="{{ old('about_founder_role', $founder['about_founder_role'] ?? '') }}" placeholder="e.g. Founder &amp; CEO">
                </div>

                <div class="form-group" style="grid-column:span 12;">
                    <label class="form-label">Biography</label>
                    <textarea name="about_founder_bio" class="form-control" rows="7" placeholder="Write the founder's story...">{{ old('about_founder_bio', $founder['about_founder_bio'] ?? '') }}</textarea>
                    <small class="form-hint">Separate paragraphs with a blank line — each becomes its own paragraph on the About page.</small>
                </div>

                <div class="field-span-6 form-group">
                    <label class="form-label">Portrait Image</label>
                    <input type="text" name="about_founder_image" id="founderImageUrl" class="form-control" value="{{ old('about_founder_image', $founder['about_founder_image'] ?? '') }}" placeholder="Paste a URL, upload, or select from the media library">
                    <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;margin-top:8px;">
                        <span class="btn-library" onclick="openMediaPicker('about_founder_image', false, founderPickImage)"><i class="mdi mdi-folder-image"></i> Select from Library</span>
                        <label class="btn btn-light btn-sm" style="margin:0;cursor:pointer;">
                            <i class="mdi mdi-upload"></i> Upload File
                            <input type="file" name="about_founder_image_file" accept="image/*" style="display:none;" onchange="founderUploadPreview(this)">
                        </label>
                    </div>
                    <small class="form-hint">Leave blank to show initials instead.</small>
                    <div id="founderImagePreview" class="preview-box" style="{{ !empty($founder['about_founder_image']) ? '' : 'display:none;' }}">
                        <img src="{{ $founder['about_founder_image'] ?? '' }}" alt="Founder">
                    </div>
                </div>
                <div class="field-span-6 form-group">
                    <label class="form-label">Initials (fallback)</label>
                    <input type="text" name="about_founder_initials" class="form-control" maxlength="4" value="{{ old('about_founder_initials', $founder['about_founder_initials'] ?? '') }}" placeholder="e.g. MA">
                    <small class="form-hint">Shown as a large monogram when no portrait image is set.</small>
                </div>
            </div>

            <button type="submit" class="btn btn-primary mt-2"><i class="mdi mdi-content-save-outline"></i> Save Founder Details</button>
        </form>
    </div>
</div>

@include('admin.partials.media-picker')

@push('scripts')
<script>
(function () {
    var urlInput = document.getElementById('founderImageUrl');
    if (urlInput) urlInput.addEventListener('input', function () { founderShowPreview(this.value.trim()); });
})();

function founderPickImage(urls) {
    var url = (urls && urls[0]) || '';
    var input = document.getElementById('founderImageUrl');
    if (input) input.value = url;
    founderShowPreview(url);
}
function founderUploadPreview(input) {
    if (input.files && input.files[0]) founderShowPreview(URL.createObjectURL(input.files[0]));
}
function founderShowPreview(src) {
    var box = document.getElementById('founderImagePreview');
    if (!box) return;
    var img = box.querySelector('img');
    if (src) { img.src = src; box.style.display = ''; }
    else { box.style.display = 'none'; }
}
</script>
@endpush

@endsection
