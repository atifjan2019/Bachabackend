@extends('layouts.admin')
@section('title', 'SEO Settings')
@section('content')

<div class="ph">
    <div>
        <h4>SEO Settings</h4>
        <div class="ph-sub">Search engine and analytics configuration</div>
    </div>
</div>

<form action="{{ route('admin.seo.update') }}" method="POST">
    @csrf @method('PUT')
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="bcard">
                <div class="bcard-head"><span class="bcard-title">Homepage Meta Tags</span></div>
                <div class="bcard-body">
                    <div class="form-group">
                        <label class="form-label">Meta Title <span class="form-hint meta-count" data-for="meta_title" data-max="60"></span></label>
                        <input type="text" name="meta_title" class="form-control" maxlength="255" value="{{ $settings['meta_title'] ?? '' }}" placeholder="Bacha Stylo — Premium Shawls & Fabrics">
                        <div class="form-hint">Used on the homepage only. Recommended: 50–60 characters</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Meta Description <span class="form-hint meta-count" data-for="meta_description" data-max="160"></span></label>
                        <textarea name="meta_description" class="form-control" rows="3" maxlength="2000" placeholder="Shop premium quality shawls...">{{ $settings['meta_description'] ?? '' }}</textarea>
                        <div class="form-hint">Recommended: 150–160 characters</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Meta Keywords</label>
                        <input type="text" name="meta_keywords" class="form-control" value="{{ $settings['meta_keywords'] ?? '' }}" placeholder="shawls, fabric, premium, pakistan">
                    </div>
                    <div class="form-group">
                        <label class="form-label">OG / Social Share Image URL</label>
                        <input type="text" name="og_image" class="form-control" value="{{ $settings['og_image'] ?? '' }}" placeholder="https://...">
                        <div class="form-hint">Used when your site is shared on social media (1200×630px recommended)</div>
                    </div>
                </div>
            </div>

            <div class="bcard">
                <div class="bcard-head"><span class="bcard-title">Products Page Meta Tags</span></div>
                <div class="bcard-body">
                    <div class="form-group">
                        <label class="form-label">Meta Title <span class="form-hint meta-count" data-for="products_meta_title" data-max="60"></span></label>
                        <input type="text" name="products_meta_title" class="form-control" maxlength="255" value="{{ $settings['products_meta_title'] ?? '' }}" placeholder="Shop All Products">
                        <div class="form-hint">Used on the all-products page (/products). Leave blank for "Shop". The site name is added automatically.</div>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Meta Description <span class="form-hint meta-count" data-for="products_meta_description" data-max="160"></span></label>
                        <textarea name="products_meta_description" class="form-control" rows="3" maxlength="2000" placeholder="Browse every Bacha Stylo piece...">{{ $settings['products_meta_description'] ?? '' }}</textarea>
                        <div class="form-hint">Recommended: 150–160 characters. Category pages use each category's own meta fields.</div>
                    </div>
                </div>
            </div>

            <div class="bcard">
                <div class="bcard-head"><span class="bcard-title">Analytics & Tracking</span></div>
                <div class="bcard-body">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label">Google Analytics ID</label>
                            <input type="text" name="google_analytics_id" class="form-control" value="{{ $settings['google_analytics_id'] ?? '' }}" placeholder="G-XXXXXXXXXX">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Facebook Pixel ID</label>
                            <input type="text" name="facebook_pixel_id" class="form-control" value="{{ $settings['facebook_pixel_id'] ?? '' }}" placeholder="1234567890">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Google Search Console Verification</label>
                            <input type="text" name="google_site_verification" class="form-control" value="{{ $settings['google_site_verification'] ?? '' }}" placeholder="Paste only the content value">
                            <div class="form-text">Paste only the content value from the Search Console meta tag, not the whole tag. If you paste the full tag, only the content value is kept.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="side-stack">
            <div class="bcard">
                <div class="bcard-head"><span class="bcard-title">Indexing</span></div>
                <div class="bcard-body">
                    <div class="form-group">
                        <label class="form-label">Robots Meta</label>
                        <select name="robots_meta" class="form-select">
                            @foreach(['index, follow', 'noindex, nofollow', 'noindex, follow', 'index, nofollow'] as $opt)
                            <option value="{{ $opt }}" {{ ($settings['robots_meta'] ?? 'index, follow') == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Canonical Base URL</label>
                        <input type="text" name="canonical_base_url" class="form-control" value="{{ $settings['canonical_base_url'] ?? '' }}" placeholder="https://bachastylo.com">
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 btn-lg">
                <i class="mdi mdi-content-save-outline"></i> Save SEO Settings
            </button>
            </div>
        </div>
    </div>
</form>
<script>
(function () {
    document.querySelectorAll('.meta-count').forEach(function (el) {
        var field = document.querySelector('[name="' + el.dataset.for + '"]');
        if (!field) return;
        var max = parseInt(el.dataset.max, 10);
        function update() {
            var n = field.value.length;
            el.textContent = n ? '(' + n + ' / ' + max + ')' : '';
            el.style.color = n > max ? '#dc2626' : '';
        }
        field.addEventListener('input', update);
        update();
    });
})();
</script>
@endsection
