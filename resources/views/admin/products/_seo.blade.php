{{-- Per-product search engine fields. Blank falls back to the product name and description. --}}
<div class="bcard">
    <div class="bcard-head"><span class="bcard-title">Search Engine (SEO)</span></div>
    <div class="bcard-body">
        <div class="form-group">
            <label class="form-label">SEO Meta Title <span class="form-hint" id="metaTitleCount"></span></label>
            <input type="text" name="meta_title" class="form-control" maxlength="255" value="{{ old('meta_title', $product->meta_title ?? '') }}" placeholder="Leave blank to use the product name" oninput="countMeta()">
            <div class="form-hint">Shown as the page title in Google. Aim for 50 to 60 characters.</div>
        </div>
        <div class="form-group" style="margin-bottom:0;">
            <label class="form-label">SEO Meta Description <span class="form-hint" id="metaDescCount"></span></label>
            <textarea name="meta_description" class="form-control" rows="3" maxlength="500" placeholder="Leave blank to use the start of the description" oninput="countMeta()">{{ old('meta_description', $product->meta_description ?? '') }}</textarea>
            <div class="form-hint">The snippet under the title in search results. Aim for 120 to 160 characters.</div>
        </div>
    </div>
</div>
<script>
function countMeta() {
    const t = document.querySelector('[name="meta_title"]').value.length;
    const d = document.querySelector('[name="meta_description"]').value.length;
    document.getElementById('metaTitleCount').textContent = t ? '(' + t + ' / 60)' : '';
    document.getElementById('metaDescCount').textContent = d ? '(' + d + ' / 160)' : '';
}
countMeta();
</script>
