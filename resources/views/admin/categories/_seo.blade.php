{{-- Category image ALT + search engine fields with the same 60 / 160 counters as products. --}}
<div class="form-group">
    <label class="form-label">Image ALT Text</label>
    <input type="text" name="image_alt" class="form-control" maxlength="255" value="{{ old('image_alt', $category?->image_alt) }}" placeholder="e.g. Hand-embroidered Chitrali caps on display">
    <div class="form-hint">Describes the category image for search engines and screen readers. Leave blank to use the category name.</div>
</div>
<div class="form-group border-top pt-3 mt-3">
    <label class="form-label">Meta Title (SEO) <span class="form-hint" id="metaTitleCount"></span></label>
    <input type="text" name="meta_title" class="form-control" maxlength="255" value="{{ old('meta_title', $category?->meta_title) }}" placeholder="Leave blank to use the category name" oninput="countMeta()">
    <div class="form-hint">Shown as the page title in Google. Aim for 50 to 60 characters.</div>
</div>
<div class="form-group">
    <label class="form-label">Meta Description (SEO) <span class="form-hint" id="metaDescCount"></span></label>
    <textarea name="meta_description" class="form-control" rows="3" maxlength="500" oninput="countMeta()">{{ old('meta_description', $category?->meta_description) }}</textarea>
    <div class="form-hint">The snippet under the title in search results. Aim for 120 to 160 characters.</div>
</div>
<script>
function countMeta() {
    const t = document.querySelector('[name="meta_title"]').value.length;
    const d = document.querySelector('[name="meta_description"]').value.length;
    const tc = document.getElementById('metaTitleCount');
    const dc = document.getElementById('metaDescCount');
    tc.textContent = t ? '(' + t + ' / 60)' : '';
    dc.textContent = d ? '(' + d + ' / 160)' : '';
    tc.style.color = t > 60 ? '#dc2626' : '';
    dc.style.color = d > 160 ? '#dc2626' : '';
}
countMeta();
</script>
