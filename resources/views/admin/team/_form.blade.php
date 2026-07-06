@php
    // Map lucide icon keys → closest Material Design Icons for the admin preview.
    $mdiMap = [
        'crown' => 'crown', 'clipboard' => 'clipboard-text-outline', 'megaphone' => 'bullhorn-outline',
        'headphones' => 'headphones', 'palette' => 'palette', 'users' => 'account-group-outline',
        'star' => 'star-outline', 'gem' => 'diamond-stone', 'shield' => 'shield-check-outline',
        'landmark' => 'bank-outline', 'repeat' => 'repeat', 'heart' => 'heart-outline',
        'sparkles' => 'shimmer', 'truck' => 'truck-outline', 'tag' => 'tag-outline', 'briefcase' => 'briefcase-outline',
    ];
    $currentIcon = old('icon', $member->icon ?? 'users');
@endphp

<div class="form-group mb-4">
    <label class="form-label">Title <span class="text-danger">*</span></label>
    <input type="text" name="title" class="form-control" required value="{{ old('title', $member->title ?? '') }}" placeholder="e.g. Operations Manager">
</div>

<div class="form-group mb-4">
    <label class="form-label">Subtitle</label>
    <input type="text" name="subtitle" class="form-control" value="{{ old('subtitle', $member->subtitle ?? '') }}" placeholder="e.g. Fulfilment & logistics">
</div>

<div class="form-group mb-4">
    <label class="form-label">Bio</label>
    <textarea name="bio" class="form-control" rows="4" placeholder="A short description of this team member / role...">{{ old('bio', $member->bio ?? '') }}</textarea>
    <small class="form-hint">Optional. Shown under the card on the About page.</small>
</div>

<div class="fieldset-grid">
    <div class="field-span-6 form-group">
        <label class="form-label">Icon</label>
        <div style="display:flex;align-items:center;gap:10px;">
            <span id="iconPreview" style="width:42px;height:42px;flex-shrink:0;display:flex;align-items:center;justify-content:center;border:1px solid var(--bd);border-radius:10px;color:var(--red);font-size:1.2rem;">
                <i class="mdi mdi-{{ $mdiMap[$currentIcon] ?? 'account-outline' }}"></i>
            </span>
            <select name="icon" id="iconSelect" class="form-select" data-mdi='@json($mdiMap)'>
                @foreach($icons as $icon)
                    <option value="{{ $icon }}" {{ $currentIcon === $icon ? 'selected' : '' }}>{{ ucfirst($icon) }}</option>
                @endforeach
            </select>
        </div>
        <small class="form-hint">Shown when no image is uploaded.</small>
    </div>
    <div class="field-span-6 form-group">
        <label class="form-label">Sort Order</label>
        <input type="number" name="sort_order" class="form-control" min="0" value="{{ old('sort_order', $member->sort_order ?? 0) }}">
        <small class="form-hint">Lower numbers appear first.</small>
    </div>
</div>

<div class="form-group mb-4">
    <label class="form-label">Image (optional)</label>
    <input type="text" name="image_url" id="teamImageUrl" class="form-control" value="{{ old('image_url', $member->image_url ?? '') }}" placeholder="Paste a URL, upload, or select from the media library">

    <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;margin-top:8px;">
        <span class="btn-library" onclick="openMediaPicker('image_url', false, teamPickImage)"><i class="mdi mdi-folder-image"></i> Select from Library</span>
        <label class="btn btn-light btn-sm" style="margin:0;cursor:pointer;">
            <i class="mdi mdi-upload"></i> Upload File
            <input type="file" name="image_file" accept="image/*" style="display:none;" onchange="teamUploadPreview(this)">
        </label>
    </div>

    <div id="teamImagePreview" class="preview-box" style="{{ !empty($member?->image_url) ? '' : 'display:none;' }}">
        <img src="{{ $member->image_url ?? '' }}" alt="Preview">
    </div>

    @if(!empty($member?->image_url))
        <label class="form-check-label" style="display:flex;align-items:center;gap:6px;margin-top:8px;cursor:pointer;">
            <input type="checkbox" name="remove_image" value="1"> Remove current image
        </label>
    @endif
</div>

<div class="form-group mb-4">
    <label class="form-check-label" style="display:flex;align-items:center;gap:8px;cursor:pointer;">
        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $member->is_active ?? true) ? 'checked' : '' }}>
        Active (visible on the About page)
    </label>
</div>

@include('admin.partials.media-picker')

@push('scripts')
<script>
(function () {
    // Icon preview
    var sel = document.getElementById('iconSelect');
    var prev = document.getElementById('iconPreview').querySelector('i');
    var map = JSON.parse(sel.getAttribute('data-mdi'));
    sel.addEventListener('change', function () {
        prev.className = 'mdi mdi-' + (map[this.value] || 'account-outline');
    });

    // Live preview when a URL is typed/pasted
    var urlInput = document.getElementById('teamImageUrl');
    if (urlInput) {
        urlInput.addEventListener('input', function () { teamShowPreview(this.value.trim()); });
    }
})();

// Media library pick → fill the URL field + preview
function teamPickImage(urls) {
    var url = (urls && urls[0]) || '';
    var input = document.getElementById('teamImageUrl');
    if (input) input.value = url;
    teamShowPreview(url);
}

// Local file chosen → preview it
function teamUploadPreview(input) {
    if (input.files && input.files[0]) {
        teamShowPreview(URL.createObjectURL(input.files[0]));
    }
}

function teamShowPreview(src) {
    var box = document.getElementById('teamImagePreview');
    if (!box) return;
    var img = box.querySelector('img');
    if (src) { img.src = src; box.style.display = ''; }
    else { box.style.display = 'none'; }
}
</script>
@endpush
